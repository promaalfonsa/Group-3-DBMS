<?php
require 'db.php';
require 'functions.php';
$pageTitle = "Add Addresses";
require 'layout.php';
if (!is_logged_in()) { header('Location: login.php'); exit; }
$uid = current_user_id();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = trim($_POST['label']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $phone = trim($_POST['phone']);
    $stmt = $mysqli->prepare("INSERT INTO addresses (user_id,label,address,city,phone) VALUES (?,?,?,?,?)");
    $stmt->bind_param('issss', $uid, $label, $address, $city, $phone);
    if ($stmt->execute()) header('Location: addresses.php'); else $err = $stmt->error;
}
?>
<style>
    /* =========================
   ADD ADDRESS PAGE
========================= */

.address-form-wrapper{

    max-width:700px;

    margin:auto;
}

.address-form-card{

    background:#fff;

    border-radius:28px;

    padding:35px;

    box-shadow:
        0 14px 35px rgba(0,0,0,.06);
}

.address-form-title{

    font-size:2.3rem;

    font-weight:800;

    margin-bottom:25px;
}

.address-form-title span{
    color:#e21b70;
}

/* LABELS */

.form-label{

    font-weight:600;

    margin-bottom:8px;

    color:#333;
}

/* INPUTS */

.form-control{

    border:2px solid #f1f1f1;

    border-radius:16px;

    padding:14px 16px;

    font-size:.95rem;

    box-shadow:none !important;

    transition:.2s ease;
}

.form-control:focus{

    border-color:#e21b70;

    box-shadow:
        0 0 0 4px rgba(226,27,112,.08) !important;
}

/* BUTTON */

.btn-khadok{

    background:
        linear-gradient(
            135deg,
            #e21b70,
            #ff4f9d
        );

    border:none;

    color:#fff;

    padding:12px 24px;

    border-radius:999px;

    font-weight:700;

    transition:.2s ease;
}

.btn-khadok:hover{

    background:#c2185f;

    color:#fff;

    transform:translateY(-1px);
}

/* ALERT */

.alert{

    border:none;

    border-radius:18px;

    padding:14px 16px;
}

/* MOBILE */

@media(max-width:768px){

    .address-form-card{
        padding:25px;
    }

    .address-form-title{
        font-size:1.8rem;
    }
}
</style>
<div class="container address-form-wrapper">

    <div class="address-form-card">

        <h2 class="address-form-title">
            Add <span>Address</span>
        </h2>

        <?php if($err): ?>

            <div class="alert alert-danger">

                <?php echo htmlspecialchars($err); ?>

            </div>

        <?php endif; ?>

        <form method="post">

            <div class="mb-3">

                <label class="form-label">
                    Label
                </label>

                <input
                    class="form-control"
                    name="label"
                    placeholder="Home, Office..."
                    required
                >

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Address
                </label>

                <textarea
                    class="form-control"
                    name="address"
                    rows="4"
                    placeholder="Enter full delivery address"
                    required
                ></textarea>

            </div>


            <div class="mb-3">

                <label class="form-label">
                    City
                </label>

                <input
                    class="form-control"
                    name="city"
                    placeholder="Dhaka"
                >

            </div>


            <div class="mb-4">

                <label class="form-label">
                    Phone
                </label>

                <input
                    class="form-control"
                    name="phone"
                    placeholder="+8801XXXXXXXXX"
                >

            </div>


            <button class="btn-khadok">

                Add Address

            </button>

        </form>

    </div>

</div>
