# Contact Book Module — Pull & Wire Instructions

## 1. Pull the Latest Code

```bash
git pull origin claude/quicksms-security-performance-dr8sw
```

## 2. Run Migrations

The Contact Book module adds 9 new tables. Run all pending migrations:

```bash
php artisan migrate
```

This creates:
| Table | Purpose |
|-------|---------|
| `contacts` | Core contact entity (UUID, E.164 mobile, JSONB custom_data) |
| `contact_custom_field_definitions` | Tenant-scoped custom field schema (EAV definitions) |
| `tags` | Colour-coded contact labels |
| `contact_tag` | Contact ↔ Tag junction |
| `contact_lists` | Static + Dynamic lists (JSONB rules) |
| `contact_list_member` | Contact ↔ Static List junction |
| `opt_out_lists` | Suppression lists (one master per account enforced) |
| `opt_out_records` | Mobile-number-keyed opt-outs (persist after contact deletion) |
| `contact_timeline_events` | Monthly-partitioned audit trail (SHA-256 msisdn_hash) |

All tables have **Row Level Security (RLS)** enabled with tenant isolation via `app.current_tenant_id`.

## 3. What Was Wired

### Models Created (app/Models/)
- `Contact.php` — Core entity with `tags()`, `lists()` relationships, `toPortalArray()`, search scope, masked mobile
- `ContactCustomFieldDefinition.php` — EAV field schema
- `Tag.php` — Colour labels with `contacts()` relationship, `refreshContactCount()`
- `ContactList.php` — Static/dynamic lists with `contacts()` relationship
- `OptOutList.php` — Suppression lists with `records()` relationship
- `OptOutRecord.php` — Individual opt-outs keyed by mobile_number
- `ContactTimelineEvent.php` — Partitioned timeline (read-only/append-only)

### API Controller (app/Http/Controllers/Api/)
- `ContactBookApiController.php` — Full CRUD + bulk operations

### API Routes Added (routes/api.php)
```
GET    /api/contacts                    — Paginated index (search, filter by status/source/tag/list)
POST   /api/contacts                    — Create contact
GET    /api/contacts/{id}               — Show contact
PUT    /api/contacts/{id}               — Update contact
DELETE /api/contacts/{id}               — Soft delete

POST   /api/contacts/bulk/add-to-list   — Bulk add to list
POST   /api/contacts/bulk/remove-from-list
POST   /api/contacts/bulk/add-tags      — Bulk add tags (auto-creates new tags)
POST   /api/contacts/bulk/remove-tags
POST   /api/contacts/bulk/delete
POST   /api/contacts/bulk/export

GET    /api/contacts/{id}/timeline      — Cursor-paginated timeline events
POST   /api/contacts/{id}/reveal-msisdn — Audit-logged MSISDN reveal

GET    /api/tags                        — List all tags
POST   /api/tags                        — Create tag
PUT    /api/tags/{id}                   — Update tag
DELETE /api/tags/{id}                   — Delete tag

GET    /api/contact-lists               — List all lists
POST   /api/contact-lists               — Create list
PUT    /api/contact-lists/{id}          — Update list
DELETE /api/contact-lists/{id}          — Delete list
POST   /api/contact-lists/{id}/members  — Add members
DELETE /api/contact-lists/{id}/members  — Remove members

GET    /api/opt-out-lists               — List opt-out lists
POST   /api/opt-out-lists               — Create opt-out list
PUT    /api/opt-out-lists/{id}          — Update opt-out list
DELETE /api/opt-out-lists/{id}          — Delete (master protected)
GET    /api/opt-out-lists/{id}/records  — List records
POST   /api/opt-out-lists/{id}/records  — Add opt-out record
DELETE /api/opt-out-records/{id}        — Remove record
```

### Controller Updates (QuickSMSController.php)
All 4 contact page methods now query the database:
- `allContacts()` — `Contact::with(['tags', 'lists'])->...`
- `lists()` — `ContactList::orderBy('name')->...` (split static/dynamic)
- `tags()` — `Tag::orderBy('name')->...`
- `optOutLists()` — `OptOutList` + `OptOutRecord::with('optOutList')->...`

### JS Services Updated (public/js/)
- `contacts-service.js` — `useMockData: false`, all mock code removed, clean fetch() calls
- `contact-timeline-service.js` — `useMockData: false`, baseUrl changed to `/api`, mock data arrays cleared

## 4. Verify the Wiring

After running migrations, verify these pages load from the database:

1. **All Contacts** — `/contacts/all` (shows empty table if no contacts in DB)
2. **Lists** — `/contacts/lists` (empty until lists are created)
3. **Tags** — `/contacts/tags` (empty until tags are created)
4. **Opt-Out Lists** — `/contacts/opt-out` (empty until opt-out lists are created)

Test the API directly:
```bash
# Create a contact
curl -X POST /api/contacts -H "Content-Type: application/json" \
  -d '{"mobile_number": "+447700900001", "first_name": "Test", "last_name": "Contact"}'

# List contacts
curl /api/contacts

# Create a tag
curl -X POST /api/tags -H "Content-Type: application/json" \
  -d '{"name": "VIP", "color": "#6f42c1"}'
```

## 5. Architecture Notes

- **Tenant Isolation**: Every model has a global scope filtering by `auth()->user()->tenant_id`. RLS is the database-level backstop.
- **Enum Columns**: PostgreSQL enums (status, source, type) are added via raw SQL after table creation since Laravel Blueprint doesn't support PG enums natively. Use `getRawOriginal()` to read enum values.
- **Custom Fields**: Definitions in `contact_custom_field_definitions`, values in `contacts.custom_data` JSONB. GIN-indexed for filtering.
- **Timeline Partitioning**: Monthly partitions from 2026-01 to 2027-12. Composite PK `(event_id, created_at)`.
- **Opt-Out Design**: Keyed by `mobile_number`, not `contact_id`. Records survive contact deletion (compliance).
