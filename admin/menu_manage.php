
<?php
require_once '../config.php';

if (!hasRole('admin')) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$message = '';
$message_type = '';

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['add_item'])) {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $category = $_POST['category'];
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $availability = isset($_POST['availability']) ? 1 : 0;
        $image = trim($_POST['image'] ?? '');
        
        $stmt = $conn->prepare("INSERT INTO menu_items (name, description, category, price, stock, availability, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // types: name(s), description(s), category(s), price(d), stock(i), availability(i), image(s)
        $stmt->bind_param("sssdiis", $name, $description, $category, $price, $stock, $availability, $image);
        
        if ($stmt->execute()) {
            $message = 'Menu item added successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to add menu item';
            $message_type = 'error';
        }
        $stmt->close();
    } elseif (isset($_POST['update_item'])) {
        $id = intval($_POST['item_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $category = $_POST['category'];
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $availability = isset($_POST['availability']) ? 1 : 0;
        $image = trim($_POST['image'] ?? '');
        
        // Keep existing image if no new URL provided
        if ($image === '') {
            $img_stmt = $conn->prepare("SELECT image FROM menu_items WHERE id = ?");
            $img_stmt->bind_param("i", $id);
            $img_stmt->execute();
            $img_stmt->bind_result($existing_image);
            if ($img_stmt->fetch()) {
                $image = $existing_image ?? '';
            }
            $img_stmt->close();
        }
        
        $stmt = $conn->prepare("UPDATE menu_items SET name = ?, description = ?, category = ?, price = ?, stock = ?, availability = ?, image = ? WHERE id = ?");
        // types: name(s), description(s), category(s), price(d), stock(i), availability(i), image(s), id(i)
        $stmt->bind_param("sssdiisi", $name, $description, $category, $price, $stock, $availability, $image, $id);
        
        if ($stmt->execute()) {
            $message = 'Menu item updated successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to update menu item';
            $message_type = 'error';
        }
        $stmt->close();
    } elseif (isset($_POST['delete_item'])) {
        $id = intval($_POST['item_id']);
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = 'Menu item deleted successfully';
            $message_type = 'success';
        } else {
            $message = 'Failed to delete menu item';
            $message_type = 'error';
        }
        $stmt->close();
    }
    
    $conn->close();
}

// Get menu items
$conn = getDBConnection();
$menu_items = $conn->query("SELECT * FROM menu_items ORDER BY category, name");
$conn->close();

$page_title = 'Manage Menu';
include '../includes/header.php';
?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Manage Menu Items</h2>
        </div>
        
        <div style="margin-bottom: 2rem;">
            <button onclick="document.getElementById('addForm').style.display='block'" class="btn btn-primary">
                Add New Item
            </button>
        </div>
        
        <div id="addForm" style="display: none; margin-bottom: 2rem;" class="card">
            <h3>Add Menu Item</h3>
            <form method="POST" action="">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control" required>
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="snacks">Snacks</option>
                            <option value="drinks">Drinks</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Image URL (optional)</label>
                    <input type="url" name="image" class="form-control" placeholder="https://...">
                    <small style="color:#6B7280;">Paste a direct image link (e.g., from Google Photos share link)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="grid grid-3">
                    <div class="form-group">
                        <label class="form-label">Price (৳)</label>
                        <input type="number" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Availability</label>
                        <input type="checkbox" name="availability" checked>
                    </div>
                </div>
                <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                <button type="button" onclick="document.getElementById('addForm').style.display='none'" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $menu_items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <br>
                            <small><?php echo htmlspecialchars($item['description']); ?></small>
                        </td>
                        <td><?php echo ucfirst($item['category']); ?></td>
                        <td>৳<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['stock']; ?></td>
                        <td>
                            <?php if ($item['availability']): ?>
                                <span class="badge badge-success">Available</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Unavailable</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="btn btn-secondary">Edit</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_item" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h2>Edit Menu Item</h2>
        <form method="POST" action="">
            <input type="hidden" name="item_id" id="edit_item_id">
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" id="edit_category" class="form-control" required>
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="snacks">Snacks</option>
                    <option value="drinks">Drinks</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Image URL (optional)</label>
                <input type="url" name="image" id="edit_image" class="form-control" placeholder="https://...">
                <small style="color:#6B7280;">Leave blank to keep the current image.</small>
            </div>
            <div class="grid grid-3">
                <div class="form-group">
                    <label class="form-label">Price (৳)</label>
                    <input type="number" name="price" id="edit_price" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" id="edit_stock" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Availability</label>
                    <input type="checkbox" name="availability" id="edit_availability">
                </div>
            </div>
            <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
            <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn btn-secondary">Cancel</button>
        </form>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('edit_item_id').value = item.id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_category').value = item.category;
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_image').value = item.image || '';
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_stock').value = item.stock;
    document.getElementById('edit_availability').checked = item.availability == 1;
    document.getElementById('editModal').style.display = 'block';
}
</script>

<?php include '../includes/footer.php'; ?>

