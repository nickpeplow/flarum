<?php
/**
 * Tags Index View
 * 
 * Displays the tags hierarchy and management interface
 */
?>

<!-- Tags Management Interface -->
<div class="row">
    <div class="col-lg-12">
        <!-- Tags Tree Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-tags me-1"></i> Tag Hierarchy
                <button class="btn btn-sm btn-light float-end ms-2" type="button" data-bs-toggle="modal" data-bs-target="#addTagModal">
                    <i class="fas fa-plus"></i> Add Tag
                </button>
            </div>
            <div class="card-body">
                <div id="tag-tree">
                    <?php if (empty($tags)): ?>
                        <div class="alert alert-info">
                            No tags have been defined yet. Create your first tag by clicking the "Add Tag" button.
                        </div>
                    <?php else: ?>
                        <?php foreach ($tags as $tag): ?>
                            <div class="tag-parent mb-3">
                                <div class="tag-item p-2 border" style="border-left: 5px solid <?php echo $tag['color']; ?> !important;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge rounded-pill" style="background-color: <?php echo $tag['color']; ?>">
                                                <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($tag['name']); ?>
                                            </span>
                                            <small class="text-muted ms-2"><?php echo htmlspecialchars($tag['description']); ?></small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="editTag(<?php echo $tag['id']; ?>, '<?php echo htmlspecialchars(addslashes($tag['name'])); ?>', '<?php echo htmlspecialchars(addslashes($tag['slug'])); ?>', '<?php echo htmlspecialchars(addslashes($tag['description'])); ?>', '<?php echo $tag['color']; ?>', '<?php echo $tag['parent_id'] ?? ''; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTag(<?php echo $tag['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($tag['children'])): ?>
                                    <div class="tag-children ms-4 mt-2">
                                        <?php foreach ($tag['children'] as $child): ?>
                                            <div class="tag-item p-2 mb-2 border" style="border-left: 5px solid <?php echo $child['color']; ?> !important;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="badge rounded-pill" style="background-color: <?php echo $child['color']; ?>">
                                                            <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($child['name']); ?>
                                                        </span>
                                                        <small class="text-muted ms-2"><?php echo htmlspecialchars($child['description']); ?></small>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editTag(<?php echo $child['id']; ?>, '<?php echo htmlspecialchars(addslashes($child['name'])); ?>', '<?php echo htmlspecialchars(addslashes($child['slug'])); ?>', '<?php echo htmlspecialchars(addslashes($child['description'])); ?>', '<?php echo $child['color']; ?>', '<?php echo $child['parent_id']; ?>')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTag(<?php echo $child['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Tag Modal -->
<div class="modal fade" id="addTagModal" tabindex="-1" aria-labelledby="addTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addTagModalLabel">Add New Tag</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTagForm">
                    <div class="mb-3">
                        <label for="tagName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="tagName" required>
                    </div>
                    <div class="mb-3">
                        <label for="tagSlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="tagSlug">
                        <div class="form-text">Leave empty to auto-generate from name</div>
                    </div>
                    <div class="mb-3">
                        <label for="tagDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="tagDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tagColor" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="tagColor" value="#3498db">
                    </div>
                    <div class="mb-3">
                        <label for="tagParent" class="form-label">Parent Tag (optional)</label>
                        <select class="form-select" id="tagParent">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTagBtn">Save Tag</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1" aria-labelledby="editTagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editTagModalLabel">Edit Tag</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTagForm">
                    <input type="hidden" id="editTagId">
                    <div class="mb-3">
                        <label for="editTagName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editTagName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTagSlug" class="form-label">Slug</label>
                        <input type="text" class="form-control" id="editTagSlug">
                        <div class="form-text">Leave empty to auto-generate from name</div>
                    </div>
                    <div class="mb-3">
                        <label for="editTagDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editTagDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editTagColor" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color" id="editTagColor">
                    </div>
                    <div class="mb-3">
                        <label for="editTagParent" class="form-label">Parent Tag (optional)</label>
                        <select class="form-select" id="editTagParent">
                            <option value="">None (Top Level)</option>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateTagBtn">Update Tag</button>
            </div>
        </div>
    </div>
</div>

<!-- Page Scripts -->
<script>
    // Function to open the Edit Tag modal with the provided tag data
    function editTag(id, name, slug, description, color, parentId) {
        // Set the form values
        document.getElementById('editTagId').value = id;
        document.getElementById('editTagName').value = name;
        document.getElementById('editTagSlug').value = slug;
        document.getElementById('editTagDescription').value = description;
        document.getElementById('editTagColor').value = color;
        
        // Set the parent tag dropdown
        const parentSelect = document.getElementById('editTagParent');
        if (parentId) {
            // Find and select the option with the matching value
            for (let i = 0; i < parentSelect.options.length; i++) {
                if (parentSelect.options[i].value == parentId) {
                    parentSelect.selectedIndex = i;
                    break;
                }
            }
        } else {
            // Select the "None" option
            parentSelect.selectedIndex = 0;
        }
        
        // Open the modal
        const editModal = new bootstrap.Modal(document.getElementById('editTagModal'));
        editModal.show();
    }
    
    function deleteTag(id) {
        if (confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
            alert('Delete tag functionality would remove tag ID: ' + id);
            // In a real app, this would send a DELETE request to the server
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle the add tag form submission
        document.getElementById('saveTagBtn').addEventListener('click', function() {
            alert('Save tag functionality would submit the form data to create a new tag');
            // In a real app, this would validate and submit the form
            document.getElementById('addTagModal').querySelector('button.btn-close').click();
        });
        
        // Handle the edit tag form submission
        document.getElementById('updateTagBtn').addEventListener('click', function() {
            const tagId = document.getElementById('editTagId').value;
            alert('Update tag functionality would save changes to tag ID: ' + tagId);
            // In a real app, this would validate and submit the form
            document.getElementById('editTagModal').querySelector('button.btn-close').click();
        });
        
        // Auto-generate slug from name in add form
        document.getElementById('tagName').addEventListener('input', function() {
            const slugField = document.getElementById('tagSlug');
            if (!slugField.value) {
                slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
        });
        
        // Auto-generate slug from name in edit form
        document.getElementById('editTagName').addEventListener('input', function() {
            const slugField = document.getElementById('editTagSlug');
            if (!slugField.value) {
                slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
        });
    });
</script> 