<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Thông tin đơn hàng mới
        </h1>
    </section>

    <!-- Main content -->
    <hr>
    <div class="new-product">
        <form action="" method="POST" role="form">
            <div class="new-product__title"></div>
            <hr>
            <div class="form-group">
                <div><label for="">*Nhập email để tìm kiếm</label>
                    <br>
                    <input type="text" id="" placeholder="Input field">
                </div>
                <div>
                    <label for="">*Chọn khóa học</label>
                    <br>
                    <select name="" id="input" required=" required">
                        <option value="">C++</option>
                        <option value="">JAVA</option>
                        <option value="">React</option>
                        <option value="">Javascript</option>
                    </select>
                </div>

                <div>
                    <label for="">Chọn giá trị khóa học</label>
                    <br>
                    <input type="text" id="" placeholder="Input field">
                </div>

                <div><label for="">Trạng thái đơn hàng</label>
                    <br>
                    <input type="text" id="" placeholder="Input field">
                </div>
            </div>



            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>


</div>


<?php include 'footer.php' ?>