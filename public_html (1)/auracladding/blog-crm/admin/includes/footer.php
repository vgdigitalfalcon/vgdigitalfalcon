        </div><!-- /.content-area -->
    </div><!-- /.main-content -->
</div><!-- /.admin-wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
// Common TinyMCE init for content editors
function initEditor(selector) {
    tinymce.init({
        selector: selector,
        height: 450,
        menubar: false,
        plugins: 'lists link image media table code codesample fullscreen preview searchreplace visualblocks wordcount autolink',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link image media | table | code fullscreen preview',
        content_style: 'body { font-family: Inter, sans-serif; font-size: 15px; line-height: 1.7; color: #1E293B; }',
        branding: false,
        promotion: false,
        images_upload_url: 'upload-image.php',
        automatic_uploads: true,
        file_picker_types: 'image',
        image_caption: true,
        image_title: true,
        image_description: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
}

// Auto slug generation
function generateSlug(title) {
    return title.toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// Character counter
function updateCharCount(input, counterId, max) {
    const len = input.value.length;
    const el = document.getElementById(counterId);
    if (!el) return;
    el.textContent = len + ' / ' + max;
    el.className = 'char-count';
    if (len > max) el.classList.add('danger');
    else if (len > max * 0.9) el.classList.add('warning');
}

// Image preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<div class="image-preview"><img src="' + e.target.result + '"><button type="button" class="remove-btn" onclick="clearImage(\'' + input.id + '\',\'' + previewId + '\')"><i class="fas fa-times"></i></button></div>';
            preview.classList.add('has-image');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function clearImage(inputId, previewId) {
    document.getElementById(inputId).value = '';
    const preview = document.getElementById(previewId);
    preview.innerHTML = '<i class="fas fa-cloud-upload-alt" style="font-size:32px;color:#94A3B8;margin-bottom:12px;"></i><p style="color:#64748B;font-size:14px;">Click or drag image here</p><p style="color:#94A3B8;font-size:12px;">JPG, PNG, WebP up to 5MB</p>';
    preview.classList.remove('has-image');
}
</script>
<?php if (!empty($extra_js)): ?>
    <?= $extra_js ?>
<?php endif; ?>
</body>
</html>
