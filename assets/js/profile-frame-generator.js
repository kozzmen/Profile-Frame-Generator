(function($) {
    'use strict';
    
    const PFG = {
        canvas: null,
        ctx: null,
        resultCanvas: null,
        resultCtx: null,
        userPhoto: null,
        frameImage: null,
        photoX: 0,
        photoY: 0,
        photoScale: 1,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        canvasSize: 800,
        
        init: function() {
            if (typeof pfgData === 'undefined') {
                return;
            }
            
            this.canvas = document.getElementById('pfg-canvas');
            this.resultCanvas = document.getElementById('pfg-result-canvas');
            
            if (!this.canvas || !this.resultCanvas) {
                return;
            }
            
            this.ctx = this.canvas.getContext('2d');
            this.resultCtx = this.resultCanvas.getContext('2d');
            
            this.setupCanvas();
            this.loadFrame();
            this.bindEvents();
        },
        
        setupCanvas: function() {
            // Set canvas size
            this.canvas.width = this.canvasSize;
            this.canvas.height = this.canvasSize;
            this.resultCanvas.width = this.canvasSize;
            this.resultCanvas.height = this.canvasSize;
        },
        
        loadFrame: function() {
            this.frameImage = new Image();
            this.frameImage.crossOrigin = 'anonymous';
            this.frameImage.onload = () => {
                $('#pfg-frame-overlay').attr('src', pfgData.frameUrl).show();
            };
            this.frameImage.src = pfgData.frameUrl;
        },
        
        bindEvents: function() {
            // Photo upload
            $('#pfg-choose-photo').on('click', () => {
                $('#pfg-photo-upload').click();
            });
            
            $('#pfg-photo-upload').on('change', (e) => {
                this.handlePhotoUpload(e.target.files[0]);
            });
            
            // Zoom control
            $('#pfg-zoom').on('input', (e) => {
                this.photoScale = parseFloat(e.target.value) / 100;
                $('#pfg-zoom-value').text(e.target.value + '%');
                this.render();
            });
            
            // Mouse events
            this.canvas.addEventListener('mousedown', (e) => this.startDrag(e));
            this.canvas.addEventListener('mousemove', (e) => this.drag(e));
            this.canvas.addEventListener('mouseup', () => this.endDrag());
            this.canvas.addEventListener('mouseleave', () => this.endDrag());
            
            // Touch events
            this.canvas.addEventListener('touchstart', (e) => this.startDrag(e.touches[0]));
            this.canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                this.drag(e.touches[0]);
            });
            this.canvas.addEventListener('touchend', () => this.endDrag());
            
            // Generate
            $('#pfg-generate').on('click', () => {
                this.generateComposite();
            });
            
            // Download
            $('#pfg-download').on('click', () => {
                this.downloadImage();
            });
            
            // Save to library
            $('#pfg-save-library').on('click', () => {
                this.saveToLibrary();
            });
            
            // Start over
            $('#pfg-start-over').on('click', () => {
                this.reset();
            });
        },
        
        handlePhotoUpload: function(file) {
            if (!file || !file.type.match('image.*')) {
                alert('Please select an image file');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.userPhoto = new Image();
                this.userPhoto.onload = () => {
                    // Center photo
                    this.photoX = (this.canvasSize - this.userPhoto.width * this.photoScale) / 2;
                    this.photoY = (this.canvasSize - this.userPhoto.height * this.photoScale) / 2;
                    
                    $('.pfg-upload-area').hide();
                    $('.pfg-editor-area').show();
                    this.render();
                };
                this.userPhoto.src = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        
        startDrag: function(e) {
            if (!this.userPhoto) return;
            
            const rect = this.canvas.getBoundingClientRect();
            const x = (e.clientX || e.pageX) - rect.left;
            const y = (e.clientY || e.pageY) - rect.top;
            
            this.isDragging = true;
            this.dragStartX = x - this.photoX;
            this.dragStartY = y - this.photoY;
        },
        
        drag: function(e) {
            if (!this.isDragging) return;
            
            const rect = this.canvas.getBoundingClientRect();
            const x = (e.clientX || e.pageX) - rect.left;
            const y = (e.clientY || e.pageY) - rect.top;
            
            this.photoX = x - this.dragStartX;
            this.photoY = y - this.dragStartY;
            
            this.render();
        },
        
        endDrag: function() {
            this.isDragging = false;
        },
        
        render: function() {
            if (!this.userPhoto) return;
            
            // Clear canvas
            this.ctx.clearRect(0, 0, this.canvasSize, this.canvasSize);
            
            // Draw user photo
            const scaledWidth = this.userPhoto.width * this.photoScale;
            const scaledHeight = this.userPhoto.height * this.photoScale;
            
            this.ctx.drawImage(
                this.userPhoto,
                this.photoX,
                this.photoY,
                scaledWidth,
                scaledHeight
            );
        },
        
        generateComposite: function() {
            if (!this.userPhoto || !this.frameImage) {
                alert('Please upload a photo first');
                return;
            }
            
            // Clear result canvas
            this.resultCtx.clearRect(0, 0, this.canvasSize, this.canvasSize);
            
            // Draw user photo
            const scaledWidth = this.userPhoto.width * this.photoScale;
            const scaledHeight = this.userPhoto.height * this.photoScale;
            
            this.resultCtx.drawImage(
                this.userPhoto,
                this.photoX,
                this.photoY,
                scaledWidth,
                scaledHeight
            );
            
            // Draw frame on top
            this.resultCtx.drawImage(
                this.frameImage,
                0,
                0,
                this.canvasSize,
                this.canvasSize
            );
            
            // Show result
            $('.pfg-editor-area').hide();
            $('.pfg-result-area').show();
        },
        
        downloadImage: function() {
            const link = document.createElement('a');
            link.download = 'profile-frame-' + Date.now() + '.png';
            link.href = this.resultCanvas.toDataURL('image/png');
            link.click();
        },
        
        saveToLibrary: function() {
            if (!pfgData.isLoggedIn) {
                alert('Please log in to save images');
                return;
            }
            
            const imageData = this.resultCanvas.toDataURL('image/png');
            const $button = $('#pfg-save-library');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(pfgData.strings.uploading);
            
            $.ajax({
                url: pfgData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'pfg_save_to_library',
                    nonce: pfgData.nonce,
                    image_data: imageData
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage(pfgData.strings.success, 'success');
                    } else {
                        this.showMessage(response.data.message || pfgData.strings.error, 'error');
                    }
                },
                error: () => {
                    this.showMessage(pfgData.strings.error, 'error');
                },
                complete: () => {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },
        
        showMessage: function(text, type) {
            const $message = $('#pfg-message');
            $message.removeClass('pfg-message-success pfg-message-error')
                   .addClass('pfg-message-' + type)
                   .text(text)
                   .show();
            
            setTimeout(() => {
                $message.fadeOut();
            }, 5000);
        },
        
        reset: function() {
            this.userPhoto = null;
            this.photoX = 0;
            this.photoY = 0;
            this.photoScale = 1;
            
            $('#pfg-zoom').val(100);
            $('#pfg-zoom-value').text('100%');
            $('#pfg-photo-upload').val('');
            
            this.ctx.clearRect(0, 0, this.canvasSize, this.canvasSize);
            this.resultCtx.clearRect(0, 0, this.canvasSize, this.canvasSize);
            
            $('.pfg-result-area').hide();
            $('.pfg-editor-area').hide();
            $('.pfg-upload-area').show();
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        PFG.init();
    });
    
})(jQuery);
