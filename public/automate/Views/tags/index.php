<?php
/**
 * Tags Index View
 * 
 * Displays the tags hierarchy and management interface
 */
?>

<!-- Tags Management Interface -->
<div class="row">
    <div class="col-lg-8">
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
                                            <button class="btn btn-sm btn-outline-primary" onclick="editTag(<?php echo $tag['id']; ?>)">
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
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editTag(<?php echo $child['id']; ?>)">
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
    
    <div class="col-lg-4">
        <!-- Tag Statistics Card -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-chart-pie me-1"></i> Tag Statistics
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>Total Tags:</div>
                    <div class="font-weight-bold"><?php echo count($tags); ?> parent tags</div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div>Child Tags:</div>
                    <div class="font-weight-bold">
                        <?php 
                            $childCount = 0;
                            foreach ($tags as $tag) {
                                $childCount += count($tag['children'] ?? []);
                            }
                            echo $childCount;
                        ?> subtags
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tag Usage Card -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle me-1"></i> About Tags
            </div>
            <div class="card-body">
                <p>Tags help organize your forum content for easier navigation.</p>
                <p>You can create a hierarchy of tags to represent categories and subcategories.</p>
                <p>Each tag can be assigned a unique color for visual identification.</p>
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

<!-- Page Scripts -->
<script>
    // This would be implemented in a real application
    function editTag(id) {
        alert('Edit tag functionality would open a modal to edit tag ID: ' + id);
        // In a real app, this would open the edit modal with the tag data
    }
    
    function deleteTag(id) {
        if (confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
            alert('Delete tag functionality would remove tag ID: ' + id);
            // In a real app, this would send a DELETE request to the server
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle the tag form submission
        document.getElementById('saveTagBtn').addEventListener('click', function() {
            alert('Save tag functionality would submit the form data to create a new tag');
            // In a real app, this would validate and submit the form
            document.getElementById('addTagModal').querySelector('button.btn-close').click();
        });
        
        // Auto-generate slug from name
        document.getElementById('tagName').addEventListener('input', function() {
            const slugField = document.getElementById('tagSlug');
            if (!slugField.value) {
                slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
        });
    });
</script> 