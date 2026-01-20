/**
 * CanonicalReviewRenderer Component
 * 
 * Reusable component for rendering submission reviews for:
 * - RCS_AGENT_SUBMISSION_VERSION
 * - SENDERID_SUBMISSION_VERSION
 * 
 * CANONICAL COMPONENT: This is the single source of truth for review rendering.
 * The core review layout is identical for both customer_review and admin_review modes.
 * Admin mode only adds metadata blocks - never changes core layout.
 */

const CanonicalReviewRenderer = (function() {
    'use strict';

    // Submission type enum
    const SubmissionType = Object.freeze({
        RCS_AGENT: 'RCS_AGENT',
        SENDERID: 'SENDERID'
    });

    // Mode enum
    const Mode = Object.freeze({
        CUSTOMER_REVIEW: 'customer_review',
        ADMIN_REVIEW: 'admin_review'
    });

    // Theme colors based on mode
    const ThemeColors = {
        customer: {
            primary: '#886cc0',
            primaryLight: '#f3e8ff',
            accent: '#a78bfa'
        },
        admin: {
            primary: '#1e3a5f',
            primaryLight: '#e8f4fd',
            accent: '#4a90d9'
        }
    };

    /**
     * Main renderer class
     */
    class Renderer {
        constructor(options) {
            this.container = typeof options.container === 'string' 
                ? document.querySelector(options.container) 
                : options.container;
            this.submissionType = options.submissionType;
            this.submissionVersionId = options.submissionVersionId || null;
            this.readOnly = options.readOnly !== false;
            this.mode = options.mode || Mode.CUSTOMER_REVIEW;
            this.data = options.data || {};
            
            this.theme = this.mode === Mode.ADMIN_REVIEW ? ThemeColors.admin : ThemeColors.customer;
            
            if (!this.container) {
                console.error('CanonicalReviewRenderer: Container not found');
                return;
            }

            if (!Object.values(SubmissionType).includes(this.submissionType)) {
                console.error('CanonicalReviewRenderer: Invalid submissionType. Must be RCS_AGENT or SENDERID');
                return;
            }

            this.render();
        }

        render() {
            this.container.innerHTML = '';
            this.container.classList.add('canonical-review-renderer');
            
            // Inject styles
            this.injectStyles();
            
            // Admin mode: Show metadata header first
            if (this.mode === Mode.ADMIN_REVIEW) {
                this.container.appendChild(this.renderAdminMetadata());
            }
            
            // Core review layout (identical for both modes)
            this.container.appendChild(this.renderCoreReview());
            
            // Admin mode: Show external status after core review
            if (this.mode === Mode.ADMIN_REVIEW) {
                this.container.appendChild(this.renderExternalStatus());
            }
        }

        injectStyles() {
            if (document.getElementById('canonical-review-renderer-styles')) return;
            
            const styles = document.createElement('style');
            styles.id = 'canonical-review-renderer-styles';
            styles.textContent = `
                .canonical-review-renderer {
                    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
                }
                
                .crr-metadata-block {
                    background: linear-gradient(135deg, ${this.theme.primaryLight} 0%, #fff 100%);
                    border: 1px solid ${this.theme.primary}20;
                    border-radius: 8px;
                    padding: 1rem;
                    margin-bottom: 1rem;
                }
                
                .crr-metadata-header {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-weight: 600;
                    color: ${this.theme.primary};
                    font-size: 0.85rem;
                    margin-bottom: 0.75rem;
                }
                
                .crr-metadata-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 0.75rem;
                }
                
                .crr-metadata-item {
                    display: flex;
                    flex-direction: column;
                    gap: 0.25rem;
                }
                
                .crr-metadata-label {
                    font-size: 0.7rem;
                    color: #64748b;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                .crr-metadata-value {
                    font-size: 0.85rem;
                    color: #1e293b;
                    font-weight: 500;
                }
                
                .crr-review-section {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 1rem;
                    margin-bottom: 1rem;
                }
                
                .crr-section-title {
                    font-weight: 600;
                    color: ${this.theme.primary};
                    font-size: 0.875rem;
                    margin-bottom: 0.75rem;
                    padding-bottom: 0.5rem;
                    border-bottom: 1px solid #f1f5f9;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                
                .crr-review-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    padding: 0.5rem 0;
                    border-bottom: 1px solid #f8fafc;
                }
                
                .crr-review-row:last-child {
                    border-bottom: none;
                }
                
                .crr-review-label {
                    font-size: 0.8rem;
                    color: #64748b;
                    font-weight: 500;
                }
                
                .crr-review-value {
                    font-size: 0.85rem;
                    color: #1e293b;
                    font-weight: 500;
                    text-align: right;
                    max-width: 60%;
                }
                
                .crr-review-value.mono {
                    font-family: 'SF Mono', 'Monaco', monospace;
                    background: #f1f5f9;
                    padding: 0.25rem 0.5rem;
                    border-radius: 4px;
                }
                
                .crr-status-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                    padding: 0.25rem 0.5rem;
                    border-radius: 4px;
                    font-size: 0.75rem;
                    font-weight: 500;
                }
                
                .crr-status-pending { background: #fef3c7; color: #92400e; }
                .crr-status-validated { background: #d1fae5; color: #065f46; }
                .crr-status-failed { background: #fee2e2; color: #991b1b; }
                .crr-status-not-started { background: #f1f5f9; color: #64748b; }
                
                .crr-external-status {
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 1rem;
                    margin-top: 1rem;
                }
                
                .crr-external-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 0.75rem;
                }
                
                .crr-external-title {
                    font-weight: 600;
                    color: ${this.theme.primary};
                    font-size: 0.85rem;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }
                
                .crr-color-preview {
                    display: inline-block;
                    width: 18px;
                    height: 18px;
                    border-radius: 4px;
                    border: 1px solid #e2e8f0;
                    vertical-align: middle;
                }
                
                .crr-thumbnail {
                    width: 60px;
                    height: 60px;
                    object-fit: cover;
                    border-radius: 4px;
                    border: 1px solid #e2e8f0;
                }
                
                .crr-thumbnail-logo {
                    border-radius: 50%;
                }
                
                .crr-admin-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.25rem;
                    background: #fef3c7;
                    color: #92400e;
                    padding: 0.15rem 0.4rem;
                    border-radius: 4px;
                    font-size: 0.65rem;
                    font-weight: 600;
                    margin-left: 0.5rem;
                }
                
                .crr-row-layout {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 1rem;
                }
            `;
            document.head.appendChild(styles);
        }

        renderAdminMetadata() {
            const metadata = this.data.metadata || {};
            const wrapper = document.createElement('div');
            wrapper.className = 'crr-metadata-block';
            wrapper.innerHTML = `
                <div class="crr-metadata-header">
                    <i class="fas fa-info-circle"></i>
                    Submission Metadata
                    <span class="crr-admin-badge"><i class="fas fa-lock"></i> Admin Only</span>
                </div>
                <div class="crr-metadata-grid">
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Submitted By</span>
                        <span class="crr-metadata-value">${this.escapeHtml(metadata.submittedBy || '-')}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Account</span>
                        <span class="crr-metadata-value">${this.escapeHtml(metadata.account || '-')}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Sub-Account</span>
                        <span class="crr-metadata-value">${this.escapeHtml(metadata.subAccount || '-')}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Submitted At</span>
                        <span class="crr-metadata-value">${this.formatDateTime(metadata.submittedAt)}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Version ID</span>
                        <span class="crr-metadata-value" style="font-family: monospace;">${this.escapeHtml(this.submissionVersionId || metadata.versionId || '-')}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Last Updated</span>
                        <span class="crr-metadata-value">${this.formatDateTime(metadata.lastUpdatedAt)}</span>
                    </div>
                </div>
            `;
            return wrapper;
        }

        renderCoreReview() {
            const wrapper = document.createElement('div');
            wrapper.className = 'crr-core-review';
            
            if (this.submissionType === SubmissionType.SENDERID) {
                wrapper.innerHTML = this.renderSenderIdReview();
            } else if (this.submissionType === SubmissionType.RCS_AGENT) {
                wrapper.innerHTML = this.renderRcsAgentReview();
            }
            
            return wrapper;
        }

        renderSenderIdReview() {
            const d = this.data;
            return `
                <div class="crr-review-section">
                    <div class="crr-section-title"><i class="fas fa-id-badge"></i> SenderID Details</div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">Type</span>
                        <span class="crr-review-value">${this.escapeHtml(d.type || '-')}</span>
                    </div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">SenderID Value</span>
                        <span class="crr-review-value mono">${this.escapeHtml(d.senderId || '-')}</span>
                    </div>
                </div>
                
                <div class="crr-review-section">
                    <div class="crr-section-title"><i class="fas fa-building"></i> Business</div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">Brand</span>
                        <span class="crr-review-value">${this.escapeHtml(d.brand || '-')}</span>
                    </div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">Country</span>
                        <span class="crr-review-value">${this.escapeHtml(d.country || 'United Kingdom')}</span>
                    </div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">Subaccount(s)</span>
                        <span class="crr-review-value">${this.escapeHtml(d.subaccounts || '-')}</span>
                    </div>
                    ${d.users ? `
                    <div class="crr-review-row">
                        <span class="crr-review-label">Users</span>
                        <span class="crr-review-value">${this.escapeHtml(d.users)}</span>
                    </div>
                    ` : ''}
                </div>
                
                <div class="crr-review-section">
                    <div class="crr-section-title"><i class="fas fa-envelope"></i> Use Case</div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">Primary Use</span>
                        <span class="crr-review-value">${this.escapeHtml(d.useCase || '-')}</span>
                    </div>
                    <div class="crr-review-row">
                        <span class="crr-review-label">Description</span>
                        <span class="crr-review-value">${this.escapeHtml(d.description || '-')}</span>
                    </div>
                </div>
            `;
        }

        renderRcsAgentReview() {
            const d = this.data;
            return `
                <div class="crr-row-layout">
                    <div>
                        <div class="crr-review-section">
                            <div class="crr-section-title"><i class="fas fa-palette"></i> Branding & Identity</div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Agent Name</span>
                                <span class="crr-review-value">${this.escapeHtml(d.agentName || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Description</span>
                                <span class="crr-review-value">${this.escapeHtml(d.description || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Brand Colour</span>
                                <span class="crr-review-value">
                                    <span class="crr-color-preview" style="background: ${this.escapeHtml(d.brandColor || '#886CC0')};"></span>
                                    ${this.escapeHtml(d.brandColor || '-')}
                                </span>
                            </div>
                            ${d.logoUrl ? `
                            <div class="crr-review-row">
                                <span class="crr-review-label">Logo</span>
                                <span class="crr-review-value"><img src="${this.escapeHtml(d.logoUrl)}" class="crr-thumbnail crr-thumbnail-logo" alt="Logo"></span>
                            </div>
                            ` : ''}
                            ${d.heroUrl ? `
                            <div class="crr-review-row">
                                <span class="crr-review-label">Hero Image</span>
                                <span class="crr-review-value"><img src="${this.escapeHtml(d.heroUrl)}" class="crr-thumbnail" alt="Hero" style="width: 120px; height: auto;"></span>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="crr-review-section">
                            <div class="crr-section-title"><i class="fas fa-headset"></i> Contact & Support</div>
                            ${d.showPhone ? `
                            <div class="crr-review-row">
                                <span class="crr-review-label">Phone</span>
                                <span class="crr-review-value">${this.escapeHtml(d.supportPhone || '-')}</span>
                            </div>
                            ` : ''}
                            ${d.showEmail ? `
                            <div class="crr-review-row">
                                <span class="crr-review-label">Email</span>
                                <span class="crr-review-value">${this.escapeHtml(d.supportEmail || '-')}</span>
                            </div>
                            ` : ''}
                            <div class="crr-review-row">
                                <span class="crr-review-label">Website</span>
                                <span class="crr-review-value">${this.escapeHtml(d.website || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Privacy Policy</span>
                                <span class="crr-review-value">${this.escapeHtml(d.privacyUrl || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Terms of Service</span>
                                <span class="crr-review-value">${this.escapeHtml(d.termsUrl || '-')}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="crr-review-section">
                            <div class="crr-section-title"><i class="fas fa-bullhorn"></i> Use Case & Messaging</div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Billing Category</span>
                                <span class="crr-review-value">${this.escapeHtml(d.billingCategory || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Primary Use Case</span>
                                <span class="crr-review-value">${this.escapeHtml(d.useCase || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Overview</span>
                                <span class="crr-review-value">${this.escapeHtml(d.useCaseOverview || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Campaign Frequency</span>
                                <span class="crr-review-value">${this.escapeHtml(d.campaignFrequency || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">User Consent</span>
                                <span class="crr-review-value">${this.escapeHtml(d.userConsent || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Opt-Out Available</span>
                                <span class="crr-review-value">${this.escapeHtml(d.optOutAvailable || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Monthly Volume</span>
                                <span class="crr-review-value">${this.escapeHtml(d.monthlyVolume || '-')}</span>
                            </div>
                        </div>
                        
                        <div class="crr-review-section">
                            <div class="crr-section-title"><i class="fas fa-building"></i> Business Information</div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Company Name</span>
                                <span class="crr-review-value">${this.escapeHtml(d.companyName || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Company Number</span>
                                <span class="crr-review-value mono">${this.escapeHtml(d.companyNumber || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Website</span>
                                <span class="crr-review-value">${this.escapeHtml(d.companyWebsite || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Sector</span>
                                <span class="crr-review-value">${this.escapeHtml(d.sector || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Address</span>
                                <span class="crr-review-value">${this.formatAddress(d)}</span>
                            </div>
                        </div>
                        
                        <div class="crr-review-section">
                            <div class="crr-section-title"><i class="fas fa-user-tie"></i> Approver Details</div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Name</span>
                                <span class="crr-review-value">${this.escapeHtml(d.approverName || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Job Title</span>
                                <span class="crr-review-value">${this.escapeHtml(d.approverJobTitle || '-')}</span>
                            </div>
                            <div class="crr-review-row">
                                <span class="crr-review-label">Email</span>
                                <span class="crr-review-value">${this.escapeHtml(d.approverEmail || '-')}</span>
                            </div>
                        </div>
                        
                        ${d.testNumbers && d.testNumbers.length > 0 ? `
                        <div class="crr-review-section">
                            <div class="crr-section-title"><i class="fas fa-mobile-alt"></i> Test Numbers</div>
                            ${d.testNumbers.map(num => `
                            <div class="crr-review-row">
                                <span class="crr-review-label">Test Number</span>
                                <span class="crr-review-value mono">${this.escapeHtml(num)}</span>
                            </div>
                            `).join('')}
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        renderExternalStatus() {
            const external = this.data.externalStatus || {};
            const statusClass = this.getStatusClass(external.status);
            
            const wrapper = document.createElement('div');
            wrapper.className = 'crr-external-status';
            wrapper.innerHTML = `
                <div class="crr-external-header">
                    <span class="crr-external-title">
                        <i class="fas fa-globe"></i>
                        External Validation Status
                        <span class="crr-admin-badge"><i class="fas fa-lock"></i> Admin Only</span>
                    </span>
                    <span class="crr-status-badge ${statusClass}">
                        <i class="fas ${this.getStatusIcon(external.status)}"></i>
                        ${this.escapeHtml(external.status || 'Not Started')}
                    </span>
                </div>
                <div class="crr-metadata-grid">
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Provider</span>
                        <span class="crr-metadata-value">${this.escapeHtml(external.provider || this.getDefaultProvider())}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Reference ID</span>
                        <span class="crr-metadata-value" style="font-family: monospace;">${this.escapeHtml(external.referenceId || '-')}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Submitted At</span>
                        <span class="crr-metadata-value">${this.formatDateTime(external.submittedAt)}</span>
                    </div>
                    <div class="crr-metadata-item">
                        <span class="crr-metadata-label">Last Checked</span>
                        <span class="crr-metadata-value">${this.formatDateTime(external.lastCheckedAt)}</span>
                    </div>
                </div>
                ${external.notes ? `
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
                    <span class="crr-metadata-label">Provider Notes</span>
                    <div style="margin-top: 0.25rem; font-size: 0.85rem; color: #475569; font-style: italic;">"${this.escapeHtml(external.notes)}"</div>
                </div>
                ` : ''}
            `;
            return wrapper;
        }

        // Utility methods
        escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        formatDateTime(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return dateStr;
            }
        }

        formatAddress(d) {
            const parts = [
                d.addressLine1,
                d.addressLine2,
                d.addressCity,
                d.addressPostCode,
                d.addressCountry
            ].filter(Boolean);
            return this.escapeHtml(parts.join(', ')) || '-';
        }

        getStatusClass(status) {
            if (!status) return 'crr-status-not-started';
            const s = status.toLowerCase();
            if (s.includes('pending') || s.includes('progress')) return 'crr-status-pending';
            if (s.includes('valid') || s.includes('approved') || s.includes('pass')) return 'crr-status-validated';
            if (s.includes('fail') || s.includes('reject')) return 'crr-status-failed';
            return 'crr-status-not-started';
        }

        getStatusIcon(status) {
            if (!status) return 'fa-minus-circle';
            const s = status.toLowerCase();
            if (s.includes('pending') || s.includes('progress')) return 'fa-clock';
            if (s.includes('valid') || s.includes('approved') || s.includes('pass')) return 'fa-check-circle';
            if (s.includes('fail') || s.includes('reject')) return 'fa-times-circle';
            return 'fa-minus-circle';
        }

        getDefaultProvider() {
            return this.submissionType === SubmissionType.SENDERID ? 'BrandAssure' : 'RCS Provider';
        }

        // Public method to update data and re-render
        update(newData) {
            this.data = { ...this.data, ...newData };
            this.render();
        }

        // Public method to change mode
        setMode(mode) {
            if (Object.values(Mode).includes(mode)) {
                this.mode = mode;
                this.theme = mode === Mode.ADMIN_REVIEW ? ThemeColors.admin : ThemeColors.customer;
                this.render();
            }
        }
    }

    // Factory function
    function create(options) {
        return new Renderer(options);
    }

    // Expose public API
    return {
        create,
        SubmissionType,
        Mode
    };
})();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CanonicalReviewRenderer;
}
