<?php 
ob_start();
include 'header.php';
if(!$customer){
    header('location: login.php');
}
$errors = [];
if(isset($_POST['name'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    if($name == ''){
        $errors['name'] = 'Họ tên không được để trống';
    }else if(strlen($name) < 3){
        $errors['name'] = 'Họ tên tối thiểu 3 ký tự';
    }
    if($phone == ''){
        $errors['phone'] = 'Số điện thoại không được để trống';
    }else if(strlen($name) < 10){
        $errors['phone'] = 'Số điện thoại tối thiểu 10 ký tự';
    }
    if($email == ''){
        $errors['email'] = 'Email không được để trống';
    }else if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $errors['phone'] = 'Email không đúng định dạng';
    }
    if($address == ''){
        $errors['address'] = 'Địa chỉ nơi ở không được để trống';
    }else if(strlen($address) < 10){
        $errors['address'] = 'Địa chỉ tối thiểu 10 ký tự';
    }
    if($password == ''){
        $errors['password'] = 'Mật khẩu không được để trống';
    }else if(!password_verify($password, $customer->password)){
        $errors['password'] = 'Mật khẩu không chính xác';
    }
    
    if(!$errors){
        $sql = "UPDATE  customer SET name = '$name', email = '$email', phone = '$phone', address = '$address' WHERE id = $customer->id";
        if($conn->query($sql)){
            header('location: logout.php');
        }else{
            $errors['failed'] = 'Cập nhật không thành công vui lòng thử lại';
        }
    }
}
?>

<!-- book section -->
<section class="book_section layout_padding">
    <div class="container">
        <div class="heading_container">
            <h2>
                Your Profile
            </h2>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php if($errors):?>
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php foreach($errors as $error) : ?>
                    <li><?php echo $error?></li>
                    <?php endforeach;?>
                </div>
                <?php endif;?>
                <div class="form_container">
                    <form action="" method="POST">
                        <div>
                            <input type="text" value="<?php echo $customer->name?>" class="form-control" name="name"
                                placeholder="Your Name" />
                        </div>
                        <div>
                            <input type="text" name="phone" value="<?php echo $customer->phone?>" class="form-control"
                                placeholder="Phone Number" />
                        </div>
                        <div>
                            <input type="email" name="email" value="<?php echo $customer->email?>" class="form-control"
                                placeholder="Your Email" />
                        </div>
                        <div>
                            <input name="address" value="<?php echo $customer->address?>" class="form-control"
                                placeholder="Your Address" />
                        </div>
                        <div>
                            <input type="password" name="password" class="form-control" placeholder="Your Password" />
                        </div>
                        <div class="btn_box">
                            <button>
                                Update profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <img width="100%"
                    src="https://www.vib.com.vn/wps/wcm/connect/07bafcaa-77ec-42f6-b36b-dc61ebedad11/token.png.webp?MOD=AJPERES&CACHEID=ROOTWORKSPACE-07bafcaa-77ec-42f6-b36b-dc61ebedad11-oKoX88E"
                    alt="">
            </div>
        </div>
    </div>
</section>
<!-- end book section -->

<!-- footer section -->
<?php include'footer.php'?>