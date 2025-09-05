// Vue.js 3 Image Upload Module
const { createApp, ref, reactive, computed, onMounted } = Vue;

createApp({
    setup() {
        // API Base URL
        const API_BASE = 'api/';
        
        // Active tab - start with multiple for testing
        const activeTab = ref('single'); // 'single', 'multiple', 'gallery'
        
        // Single upload state
        const single = reactive({
            selectedFile: null,
            uploading: false,
            progress: 0,
            error: '',
            success: '',
            isDragOver: false
        });
        
        // Multiple upload state
        const multiple = reactive({
            selectedFiles: [],
            totalSize: 0,
            uploading: false,
            progress: 0,
            error: '',
            success: '',
            isDragOver: false
        });
        
        // Gallery state
        const gallery = reactive({
            images: [],
            loading: false,
            filters: {
                type: 'all',
                sortBy: 'uploaded_at',
                sortOrder: 'DESC',
                limit: 24
            },
            pagination: {
                totalCount: 0,
                totalPages: 0,
                currentPage: 1,
                hasNextPage: false,
                hasPrevPage: false
            }
        });
        
        // Selected image for modal
        const selectedImage = ref(null);
        
        // Validation configuration
        const validation = {
            maxFileSize: 5 * 1024 * 1024, // 5MB
            maxTotalSize: 50 * 1024 * 1024, // 50MB
            maxFiles: 10,
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
            allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            maxWidth: 4000,
            maxHeight: 4000,
            minWidth: 50,
            minHeight: 50
        };

        // API Helper Functions
        const apiCall = async (url, options = {}) => {
            try {
                const response = await fetch(url, options);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'API request failed');
                }

                return data;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        };

        // File validation
        const validateFile = (file) => {
            const errors = [];
            
            // Check file size
            if (file.size > validation.maxFileSize) {
                errors.push(`File size (${formatBytes(file.size)}) exceeds maximum allowed size (${formatBytes(validation.maxFileSize)})`);
            }
            
            if (file.size <= 0) {
                errors.push('File is empty');
            }
            
            // Check file type
            if (!validation.allowedTypes.includes(file.type)) {
                errors.push(`Invalid file type. Allowed types: ${validation.allowedTypes.join(', ')}`);
            }
            
            // Check file extension
            const extension = file.name.split('.').pop().toLowerCase();
            if (!validation.allowedExtensions.includes(extension)) {
                errors.push(`Invalid file extension. Allowed extensions: ${validation.allowedExtensions.join(', ')}`);
            }
            
            return errors;
        };

        // Validate image dimensions (requires loading the image)
        const validateImageDimensions = (file) => {
            return new Promise((resolve) => {
                const img = new Image();
                const url = URL.createObjectURL(file);
                
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    const errors = [];
                    
                    if (img.width > validation.maxWidth || img.height > validation.maxHeight) {
                        errors.push(`Image dimensions (${img.width}x${img.height}) exceed maximum allowed dimensions (${validation.maxWidth}x${validation.maxHeight})`);
                    }
                    
                    if (img.width < validation.minWidth || img.height < validation.minHeight) {
                        errors.push(`Image dimensions (${img.width}x${img.height}) are below minimum required dimensions (${validation.minWidth}x${validation.minHeight})`);
                    }
                    
                    resolve(errors);
                };
                
                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    resolve(['Invalid image file']);
                };
                
                img.src = url;
            });
        };

        // Format bytes to human readable format
        const formatBytes = (bytes, decimals = 2) => {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        };

        // Clear messages
        const clearMessages = (type) => {
            if (type === 'single') {
                single.error = '';
                single.success = '';
            } else if (type === 'multiple') {
                multiple.error = '';
                multiple.success = '';
            }
        };

        // Single file upload handlers
        const handleSingleFileSelect = (event) => {
            const file = event.target.files[0];
            if (file) {
                processSingleFile(file);
            }
        };

        const handleSingleDrop = (event) => {
            single.isDragOver = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                processSingleFile(files[0]);
            }
        };

        // Click handler for single upload area
        const clickSingleUpload = () => {
            const fileInput = document.querySelector('input[type="file"]:not([multiple])');
            if (fileInput) {
                fileInput.click();
            }
        };

        const processSingleFile = async (file) => {
            clearMessages('single');
            
            // Basic validation
            const basicErrors = validateFile(file);
            if (basicErrors.length > 0) {
                single.error = basicErrors.join(', ');
                return;
            }
            
            // Dimension validation
            const dimensionErrors = await validateImageDimensions(file);
            if (dimensionErrors.length > 0) {
                single.error = dimensionErrors.join(', ');
                return;
            }
            
            single.selectedFile = file;
        };

        const uploadSingleImage = async () => {
            if (!single.selectedFile) return;
            
            try {
                single.uploading = true;
                single.progress = 0;
                clearMessages('single');
                
                const formData = new FormData();
                formData.append('image', single.selectedFile);
                
                // Simulate progress
                const progressInterval = setInterval(() => {
                    if (single.progress < 90) {
                        single.progress += Math.random() * 10;
                    }
                }, 100);
                
                const response = await apiCall(API_BASE + 'upload-single.php', {
                    method: 'POST',
                    body: formData
                });
                
                clearInterval(progressInterval);
                single.progress = 100;
                
                if (response.success) {
                    single.success = response.message;
                    single.selectedFile = null;
                    
                    // Reset file input
                    if (this.$refs && this.$refs.singleFileInput) {
                        this.$refs.singleFileInput.value = '';
                    }
                    
                    // Refresh gallery if it's active
                    if (activeTab.value === 'gallery') {
                        loadImages();
                    }
                } else {
                    single.error = response.message;
                }
                
            } catch (error) {
                single.error = error.message;
            } finally {
                single.uploading = false;
                setTimeout(() => {
                    single.progress = 0;
                    clearMessages('single');
                }, 3000);
            }
        };

        // Multiple files upload handlers
        const handleMultipleFileSelect = (event) => {
            const files = Array.from(event.target.files);
            processMultipleFiles(files);
        };

        const handleMultipleDrop = (event) => {
            multiple.isDragOver = false;
            const files = Array.from(event.dataTransfer.files);
            processMultipleFiles(files);
        };

        const processMultipleFiles = async (files) => {
            clearMessages('multiple');
            
            // Check file count
            if (files.length > validation.maxFiles) {
                multiple.error = `Too many files. Maximum allowed: ${validation.maxFiles}`;
                return;
            }
            
            const validFiles = [];
            let totalSize = 0;
            const errors = [];
            
            for (const file of files) {
                // Basic validation
                const basicErrors = validateFile(file);
                if (basicErrors.length > 0) {
                    errors.push(`${file.name}: ${basicErrors.join(', ')}`);
                    continue;
                }
                
                // Dimension validation
                const dimensionErrors = await validateImageDimensions(file);
                if (dimensionErrors.length > 0) {
                    errors.push(`${file.name}: ${dimensionErrors.join(', ')}`);
                    continue;
                }
                
                validFiles.push(file);
                totalSize += file.size;
            }
            
            // Check total size
            if (totalSize > validation.maxTotalSize) {
                errors.push(`Total file size (${formatBytes(totalSize)}) exceeds maximum allowed (${formatBytes(validation.maxTotalSize)})`);
            }
            
            if (errors.length > 0) {
                multiple.error = errors.join('; ');
                return;
            }
            
            multiple.selectedFiles = validFiles;
            multiple.totalSize = totalSize;
        };

        const removeSelectedFile = (index) => {
            multiple.selectedFiles.splice(index, 1);
            multiple.totalSize = multiple.selectedFiles.reduce((total, file) => total + file.size, 0);
        };

        const uploadMultipleImages = async () => {
            if (multiple.selectedFiles.length === 0) return;
            
            try {
                multiple.uploading = true;
                multiple.progress = 0;
                clearMessages('multiple');
                
                const formData = new FormData();
                multiple.selectedFiles.forEach(file => {
                    formData.append('images[]', file);
                });
                
                // Simulate progress
                const progressInterval = setInterval(() => {
                    if (multiple.progress < 90) {
                        multiple.progress += Math.random() * 5;
                    }
                }, 200);
                
                const response = await apiCall(API_BASE + 'upload-multiple.php', {
                    method: 'POST',
                    body: formData
                });
                
                clearInterval(progressInterval);
                multiple.progress = 100;
                
                if (response.success) {
                    multiple.success = response.message;
                    multiple.selectedFiles = [];
                    multiple.totalSize = 0;
                    
                    // Reset file input
                    if (this.$refs && this.$refs.multipleFileInput) {
                        this.$refs.multipleFileInput.value = '';
                    }
                    
                    // Refresh gallery if it's active
                    if (activeTab.value === 'gallery') {
                        loadImages();
                    }
                } else {
                    multiple.error = response.message;
                }
                
            } catch (error) {
                multiple.error = error.message;
            } finally {
                multiple.uploading = false;
                setTimeout(() => {
                    multiple.progress = 0;
                    clearMessages('multiple');
                }, 3000);
            }
        };

        // Gallery functions
        const loadImages = async () => {
            try {
                gallery.loading = true;
                
                const offset = (gallery.pagination.currentPage - 1) * gallery.filters.limit;
                const params = new URLSearchParams({
                    type: gallery.filters.type,
                    sort_by: gallery.filters.sortBy,
                    sort_order: gallery.filters.sortOrder,
                    limit: gallery.filters.limit,
                    offset: offset
                });
                
                const response = await apiCall(API_BASE + `images.php?${params}`);
                
                if (response.success) {
                    gallery.images = response.data.images;
                    gallery.pagination = response.data.pagination;
                }
                
            } catch (error) {
                console.error('Error loading images:', error);
            } finally {
                gallery.loading = false;
            }
        };

        const changePage = (page) => {
            if (page >= 1 && page <= gallery.pagination.totalPages) {
                gallery.pagination.currentPage = page;
                loadImages();
            }
        };

        const viewImage = (image) => {
            selectedImage.value = image;
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        };

        const closeImageModal = () => {
            selectedImage.value = null;
            const modal = bootstrap.Modal.getInstance(document.getElementById('imageModal'));
            if (modal) {
                modal.hide();
            }
        };

        const deleteImage = async (imageId) => {
            if (!confirm('Are you sure you want to delete this image?')) {
                return;
            }
            
            try {
                const response = await apiCall(API_BASE + 'delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ image_id: imageId })
                });
                
                if (response.success) {
                    // Remove image from gallery
                    const index = gallery.images.findIndex(img => img.id === imageId);
                    if (index !== -1) {
                        gallery.images.splice(index, 1);
                    }
                    
                    // Update pagination
                    gallery.pagination.totalCount--;
                    
                    alert('Image deleted successfully');
                } else {
                    alert('Error deleting image: ' + response.message);
                }
                
            } catch (error) {
                alert('Error deleting image: ' + error.message);
            }
        };

        const deleteAllImages = async () => {
            if (!confirm('Are you sure you want to delete ALL images? This action cannot be undone.')) {
                return;
            }
            
            try {
                const imageIds = gallery.images.map(img => img.id);
                
                const response = await apiCall(API_BASE + 'delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ image_ids: imageIds })
                });
                
                if (response.success) {
                    gallery.images = [];
                    gallery.pagination.totalCount = 0;
                    gallery.pagination.currentPage = 1;
                    
                    alert('All images deleted successfully');
                } else {
                    alert('Error deleting images: ' + response.message);
                }
                
            } catch (error) {
                alert('Error deleting images: ' + error.message);
            }
        };

        // Initialize
        onMounted(() => {
            // Load images if gallery tab is active
            if (activeTab.value === 'gallery') {
                loadImages();
            }
        });

        // Return reactive data and methods
        return {
            // Data
            activeTab,
            single,
            multiple,
            gallery,
            selectedImage,
            
            // Methods
            handleSingleFileSelect,
            handleSingleDrop,
            uploadSingleImage,
            handleMultipleFileSelect,
            handleMultipleDrop,
            removeSelectedFile,
            uploadMultipleImages,
            loadImages,
            changePage,
            viewImage,
            closeImageModal,
            deleteImage,
            deleteAllImages,
            formatBytes
        };
    }
}).mount('#app');