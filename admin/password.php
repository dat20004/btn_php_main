<?php include 'header.php' ;
$errors = [];
$id = $admin->id;
if(isset($_POST['old_password'])){
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];
    if($old_password == ''){
        $errors['old_password'] = 'Bạn phải nhập mật khẩu cũ';
    }else{
        if($new_password == ''){
            $errors['new_password'] = 'Bạn phải nhập mật khẩu mới';
        }
        if($confirm_new_password == ''){
            $errors['confirm_new_password'] = 'Bạn phải xác nhận mật khẩu mới';
        }
        else if($new_password != $confirm_new_password){
            $errors['new_password'] = 'Xác nhận mật khẩu không chính xác';
        }
        if(!$errors){
            $sqlCheck = "SELECT password FROM users where id = '$id' AND password = '$old_password'";
            $query = $pdo->query($sqlCheck);
            if($query->num_rows==0){
                $errors['failed'] = 'Mật khẩu cũ không chính xác';
            }
            else{
                $sqlUpdate = "UPDATE admin SET password = '$new_password' WHERE id = $admin->id";
               if($pdo->query($sqlUpdate)){
                unset($_SESSION['admin_login']);
                header('location: login.php');
               }else{
                $errors['failed'] = 'Có lỗi,vui lòng thử lại';  
               }
               
            }
        }
    } 
}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Change password
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body">
                <?php if($errors) : ?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

                    <?php foreach($errors as $error) :?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <form action="" method="POST" role="form">

                    <div class="form-group">
                        <label for="">Current password</label>
                        <input type="password" name="old_password" class="form-control" id="" placeholder="Input field">
                    </div>

                    <div class="form-group">
                        <label for="">New password</label>
                        <input type="password" name="new_password" class="form-control" id="" placeholder="Input field">
                    </div>
                    <div class="form-group">
                        <label for="">Confirm new password</label>
                        <input type="password" name="confirm_new_password" class="form-control" id=""
                            placeholder="Input field">
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Change password</button>
                </form>

            </div>
        </div>
    </section>
</div>

<?php include 'footer.php' ?>