<?php include 'header.php' ;
$errors = [];
$id = $admin->id;
if(isset($_POST['name'])){

    $email = $_POST['email'];
    $name = $_POST['name'];
    $old_password = $_POST['old_password'];
    if($name == ''){
        $errors['name'] = 'Bạn phải nhập họ tên';
    }

    if($email == ''){
        $errors['email'] = 'Email không được để trống';
    }else if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $errors['email'] = 'Email không đúng định dạng';
    }

    if($old_password == ''){
        $errors['old_password'] = 'Bạn phải nhập mật khẩu cũ';
    }
    
    if(!$errors){
        $sqlCheck = "SELECT  email from users WHERE email = '$email' AND id = '$id'";
        $query = $conn->query($sqlCheck);
        if($query ->num_rows == 1){
           $errors['email'] = 'Email này đã được sử dụng, chọn email khác';
        }
        else{
            $sqlUpdate = "UPDATE users SET name = '$name' , email = '$email' WHERE id = $admin->id";
               if($conn->query($sqlUpdate)){
                unset($_SESSION['admin_login']);
                header('location: login.php');
               }else{
                $errors['failed'] = 'Có lỗi,vui lòng thử lại';  
               }
        }
    }

}

?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Update profile
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
                        <label for="">Full name</label>
                        <input type="text" value="<?php echo $admin->name?>" name="name" class="form-control"
                            placeholder="Input field">
                    </div>

                    <div class="form-group">
                        <label for="">Email</label>
                        <input type="email" value="<?php echo $admin->email?>" name="email" class="form-control"
                            placeholder="Input field">
                    </div>

                    <div class="form-group">
                        <label for="">Current password</label>
                        <input type="password" name="old_password" class="form-control" placeholder="Input field">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Change password</button>
                </form>

            </div>
        </div>
    </section>
</div>

<?php include 'footer.php' ?>