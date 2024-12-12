
// <!-- Optional JavaScript for handling actions like Edit and Delete -->

function editProduct(id) {
    window.location.href = 'edit_product.php?id=' + id;
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        window.location.href = 'delete_product.php?id=' + id;
    }
}
