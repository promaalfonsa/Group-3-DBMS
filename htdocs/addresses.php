<?php
require 'db.php';
require 'functions.php';
$pageTitle = "Addresses";
require 'layout.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$uid = current_user_id();
$res = $mysqli->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY id DESC");
$res->bind_param('i', $uid); $res->execute(); $addrs = $res->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<style>
    /* =========================
   ADDRESS PAGE
========================= */

.address-wrapper{

    max-width:1100px;

    margin:auto;
}

.address-title{

    font-size:2.3rem;

    font-weight:800;

    margin-bottom:20px;
}

.address-title span{
    color:#e21b70;
}

/* TOP ACTIONS */

.address-actions{

    display:flex;

    gap:12px;

    align-items:center;

    margin-bottom:24px;

    flex-wrap:wrap;
}

.btn-khadok{

    background:
        linear-gradient(
            135deg,
            #e21b70,
            #ff4f9d
        );

    border:none;

    color:#fff;

    padding:12px 20px;

    border-radius:999px;

    text-decoration:none;

    font-weight:600;

    transition:.2s ease;
}

.btn-khadok:hover{

    background:#c2185f;

    color:#fff;

    transform:translateY(-1px);
}

.btn-back{

    background:#fff;

    border:2px solid #ddd;

    color:#333;

    padding:10px 18px;

    border-radius:999px;

    text-decoration:none;

    font-weight:600;

    transition:.2s ease;
}

.btn-back:hover{

    border-color:#e21b70;

    color:#e21b70;
}

/* TABLE */

.address-table{

    background:#fff;

    border-radius:24px;

    overflow:hidden;

    box-shadow:
        0 12px 30px rgba(0,0,0,.06);
}

.address-table table{
    margin:0;
}

.address-table th{

    background:#fff0f6;

    color:#111;

    border:none;

    padding:18px 20px;

    font-weight:700;
}

.address-table td{

    padding:18px 20px;

    vertical-align:middle;

    border-color:#f1f1f1;
}

.address-table tr:hover{
    background:#fcfcfc;
}

/* ACTION BUTTONS */

.btn-edit{

    background:#f3f4f6;

    color:#111;

    border:none;

    border-radius:999px;

    padding:8px 16px;

    text-decoration:none;

    font-size:.85rem;

    font-weight:600;

    margin-right:6px;

    transition:.2s ease;
}

.btn-edit:hover{

    background:#e5e7eb;

    color:#111;
}

.btn-delete{

    background:#ef4444;

    color:#fff;

    border:none;

    border-radius:999px;

    padding:8px 16px;

    text-decoration:none;

    font-size:.85rem;

    font-weight:600;

    transition:.2s ease;
}

.btn-delete:hover{

    background:#dc2626;

    color:#fff;
}

/* MOBILE */

@media(max-width:768px){

    .address-title{
        font-size:1.8rem;
    }

    .address-table{
        overflow-x:auto;
    }

    .address-actions{
        flex-direction:column;
        align-items:flex-start;
    }
    }</style>

<div class="container address-wrapper">

    <h2 class="address-title">
        Your <span>Addresses</span>
    </h2>

    <div class="address-actions">

        <a class="btn-khadok" href="add_address.php">
            Add Address
        </a>

        <a class="btn-back" href="profile.php">
            Back to Profile
        </a>

    </div>

    <div class="address-table">

        <table class="table">

            <tr>

                <th>Label</th>
                <th>Address</th>
                <th>City</th>
                <th>Phone</th>
                <th>Action</th>

            </tr>

            <?php foreach($addrs as $a): ?>

            <tr>

                <td>
                    <?php echo htmlspecialchars($a['label']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($a['address']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($a['city']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($a['phone']); ?>
                </td>

                <td>

                    <a
                        class="btn-edit"
                        href="edit_address.php?id=<?php echo $a['id']; ?>"
                    >
                        Edit
                    </a>

                    <a
                        class="btn-delete"
                        href="delete_address.php?id=<?php echo $a['id']; ?>"
                        onclick="return confirm('Delete?')"
                    >
                        Delete
                    </a>

                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>
