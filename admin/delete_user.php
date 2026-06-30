<form method="POST"
      action="delete_user.php"
      style="display:inline;">

    <input
        type="hidden"
        name="csrf_token"
        value="<?= csrf_token(); ?>">

    <input
        type="hidden"
        name="id"
        value="<?= $user['id']; ?>">

    <button
        class="btn btn-danger btn-sm"
        onclick="return confirm('Delete User?');">
        Delete
    </button>

</form>