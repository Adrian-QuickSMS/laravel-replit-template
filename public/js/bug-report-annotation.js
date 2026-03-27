/**
 * Bug Report Annotation — Canvas-based screenshot annotation tools
 *
 * Tools: Freehand, Arrow, Rectangle, Text
 * Pure vanilla JS, no dependencies — uses Canvas 2D API only.
 */

var BugReportAnnotation = (function() {
    'use strict';

    var canvas, ctx;
    var baseImage = null;
    var currentTool = 'freehand';
    var currentColor = '#ff0000';
    var lineWidth = 3;
    var isDrawing = false;
    var startX = 0, startY = 0;

    // History for undo
    var history = [];
    var currentPath = [];

    // Scale factor for retina displays
    var scale = 1;

    function init(canvasId, img) {
        canvas = document.getElementById(canvasId);
        if (!canvas) return;

        ctx = canvas.getContext('2d');
        baseImage = img;
        history = [];
        currentPath = [];

        // Size canvas to image (max width constrained by container)
        var container = canvas.parentElement;
        var maxWidth = container ? container.clientWidth : 700;
        scale = Math.min(1, maxWidth / img.width);

        canvas.width = img.width * scale;
        canvas.height = img.height * scale;
        canvas.style.width = canvas.width + 'px';
        canvas.style.height = canvas.height + 'px';

        // Draw base image
        redraw();

        // Bind canvas events
        canvas.addEventListener('mousedown', onMouseDown);
        canvas.addEventListener('mousemove', onMouseMove);
        canvas.addEventListener('mouseup', onMouseUp);
        canvas.addEventListener('mouseleave', onMouseUp);

        // Touch support
        canvas.addEventListener('touchstart', onTouchStart, { passive: false });
        canvas.addEventListener('touchmove', onTouchMove, { passive: false });
        canvas.addEventListener('touchend', onTouchEnd);

        // Toolbar events
        bindToolbar();
    }

    function bindToolbar() {
        // Tool selection
        var toolBtns = document.querySelectorAll('#bugAnnotationContainer [data-tool]');
        for (var i = 0; i < toolBtns.length; i++) {
            toolBtns[i].addEventListener('click', function() {
                currentTool = this.dataset.tool;
                // Update active state
                for (var j = 0; j < toolBtns.length; j++) {
                    toolBtns[j].classList.toggle('active', toolBtns[j] === this);
                }
                canvas.style.cursor = currentTool === 'text' ? 'text' : 'crosshair';
            });
        }

        // Color picker
        var colorInput = document.getElementById('bugAnnotationColor');
        if (colorInput) {
            colorInput.addEventListener('input', function() {
                currentColor = this.value;
            });
        }

        // Undo
        document.getElementById('bugAnnotationUndo')?.addEventListener('click', function() {
            if (history.length > 0) {
                history.pop();
                redraw();
            }
        });

        // Clear all
        document.getElementById('bugAnnotationClear')?.addEventListener('click', function() {
            history = [];
            redraw();
        });
    }

    function getCanvasCoords(e) {
        var rect = canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    function getTouchCoords(e) {
        var rect = canvas.getBoundingClientRect();
        var touch = e.touches[0] || e.changedTouches[0];
        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    // ─── Mouse Events ───

    function onMouseDown(e) {
        var coords = getCanvasCoords(e);
        startDrawing(coords.x, coords.y);
    }

    function onMouseMove(e) {
        if (!isDrawing) return;
        var coords = getCanvasCoords(e);
        continueDrawing(coords.x, coords.y);
    }

    function onMouseUp(e) {
        if (!isDrawing) return;
        var coords = getCanvasCoords(e);
        finishDrawing(coords.x, coords.y);
    }

    // ─── Touch Events ───

    function onTouchStart(e) {
        e.preventDefault();
        var coords = getTouchCoords(e);
        startDrawing(coords.x, coords.y);
    }

    function onTouchMove(e) {
        e.preventDefault();
        if (!isDrawing) return;
        var coords = getTouchCoords(e);
        continueDrawing(coords.x, coords.y);
    }

    function onTouchEnd(e) {
        if (!isDrawing) return;
        var coords = getTouchCoords(e);
        finishDrawing(coords.x, coords.y);
    }

    // ─── Drawing Logic ───

    function startDrawing(x, y) {
        if (currentTool === 'text') {
            addTextLabel(x, y);
            return;
        }

        isDrawing = true;
        startX = x;
        startY = y;
        currentPath = [{ x: x, y: y }];
    }

    function continueDrawing(x, y) {
        if (currentTool === 'freehand') {
            currentPath.push({ x: x, y: y });
            // Live preview
            redraw();
            drawFreehand(currentPath, currentColor, lineWidth);
        } else {
            // Live preview for arrow/rectangle
            redraw();
            if (currentTool === 'arrow') {
                drawArrow(startX, startY, x, y, currentColor, lineWidth);
            } else if (currentTool === 'rectangle') {
                drawRectangle(startX, startY, x, y, currentColor, lineWidth);
            }
        }
    }

    function finishDrawing(x, y) {
        isDrawing = false;

        var item = {
            tool: currentTool,
            color: currentColor,
            lineWidth: lineWidth
        };

        if (currentTool === 'freehand') {
            currentPath.push({ x: x, y: y });
            item.points = currentPath.slice();
        } else if (currentTool === 'arrow') {
            item.x1 = startX; item.y1 = startY;
            item.x2 = x; item.y2 = y;
        } else if (currentTool === 'rectangle') {
            item.x1 = startX; item.y1 = startY;
            item.x2 = x; item.y2 = y;
        }

        // Only add if the user actually drew something
        if (currentTool === 'freehand' && currentPath.length > 1) {
            history.push(item);
        } else if (currentTool !== 'freehand' && (Math.abs(x - startX) > 3 || Math.abs(y - startY) > 3)) {
            history.push(item);
        }

        currentPath = [];
        redraw();
    }

    function addTextLabel(x, y) {
        var text = prompt('Enter label text (max 200 chars):');
        if (!text) return;
        text = text.substring(0, 200);

        history.push({
            tool: 'text',
            color: currentColor,
            x: x,
            y: y,
            text: text
        });
        redraw();
    }

    // ─── Render Functions ───

    function redraw() {
        if (!ctx || !baseImage) return;

        // Draw base image
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(baseImage, 0, 0, canvas.width, canvas.height);

        // Draw all history items
        for (var i = 0; i < history.length; i++) {
            renderItem(history[i]);
        }
    }

    function renderItem(item) {
        switch (item.tool) {
            case 'freehand':
                drawFreehand(item.points, item.color, item.lineWidth);
                break;
            case 'arrow':
                drawArrow(item.x1, item.y1, item.x2, item.y2, item.color, item.lineWidth);
                break;
            case 'rectangle':
                drawRectangle(item.x1, item.y1, item.x2, item.y2, item.color, item.lineWidth);
                break;
            case 'text':
                drawText(item.x, item.y, item.text, item.color);
                break;
        }
    }

    function drawFreehand(points, color, lw) {
        if (points.length < 2) return;
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = lw;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.moveTo(points[0].x, points[0].y);
        for (var i = 1; i < points.length; i++) {
            ctx.lineTo(points[i].x, points[i].y);
        }
        ctx.stroke();
    }

    function drawArrow(x1, y1, x2, y2, color, lw) {
        var headLen = 15;
        var angle = Math.atan2(y2 - y1, x2 - x1);

        // Shaft
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = lw;
        ctx.lineCap = 'round';
        ctx.moveTo(x1, y1);
        ctx.lineTo(x2, y2);
        ctx.stroke();

        // Arrowhead
        ctx.beginPath();
        ctx.fillStyle = color;
        ctx.moveTo(x2, y2);
        ctx.lineTo(
            x2 - headLen * Math.cos(angle - Math.PI / 6),
            y2 - headLen * Math.sin(angle - Math.PI / 6)
        );
        ctx.lineTo(
            x2 - headLen * Math.cos(angle + Math.PI / 6),
            y2 - headLen * Math.sin(angle + Math.PI / 6)
        );
        ctx.closePath();
        ctx.fill();
    }

    function drawRectangle(x1, y1, x2, y2, color, lw) {
        ctx.beginPath();
        ctx.strokeStyle = color;
        ctx.lineWidth = lw;
        ctx.lineJoin = 'miter';
        ctx.rect(x1, y1, x2 - x1, y2 - y1);
        ctx.stroke();
    }

    function drawText(x, y, text, color) {
        ctx.font = 'bold 16px sans-serif';
        ctx.fillStyle = color;

        // Background for readability
        var metrics = ctx.measureText(text);
        var padding = 4;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
        ctx.fillRect(
            x - padding,
            y - 16 - padding,
            metrics.width + padding * 2,
            20 + padding * 2
        );

        ctx.fillStyle = color;
        ctx.fillText(text, x, y);
    }

    // ─── Public API ───
    return {
        init: init
    };
})();
