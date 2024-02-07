
<div class="container-fluid nav-bar bg-transparent">
    <nav class="navbar navbar-expand-lg bg-white navbar-light py-0 px-4">
        <a  class="navbar-brand d-flex align-items-center text-center">
            <div class="icon p-2 me-2">
                <img class="img-fluid" src="img/tv.png" alt="Icon" style="width: 30px; height: 30px;">
            </div>
            <h1 class="m-0 text-primary">ระบบแจ้งซ่อม</h1>
        </a>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto">
                <?php if (!isset($_SESSION['p_id_repair']) || $_SESSION['p_id_repair'] == 0) { ?>
                    <a href="index.php" class="nav-item nav-link active">เข้าสู่ระบบ</a>
                    <a href="register.php" class="nav-item nav-link ">ลงทะเบียน</a>
                    <a href="forgot-password.php" class="nav-item nav-link ">ลืมรหัสผ่าน</a>
                <?php } ?>

                <?php if (isset($_SESSION['p_id_repair']) && $_SESSION['p_id_repair'] > 0) { ?>
                    <a href="service.php" class="nav-item nav-link">แจ้งซ่อม</a>
                    <a href="service1.php" class="nav-item nav-link">ดูประวัติแจ้งซ่อม</a>
                    <a href="logout.php" class="nav-item nav-link active">ออกจากระบบ</a>
                <?php } ?>
                
            </div>
        </div>
    </nav>
</div>