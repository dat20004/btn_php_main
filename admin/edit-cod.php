<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Xử lý đơn hàng COD
        </h1>
    </section>

    <hr style="background-color:black;">

    <!-- Main content -->


    <div class="edit-cod">
        <form action="" method="POST" role="form">
            <select name="" id="input" required="required">
                <option value="">Nhân viên xử lý</option>
            </select>
            <select name="" id="input" required="required">
                <option value="">toàn bộ thời gian</option>
            </select>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã đơn hàng</th>
                <th>TT</th>
                <th>Khách hàng</th>
                <th>Email</th>
                <th>Khóa học</th>
                <th>Ngày mua</th>
                <th>Trạng thái</th>
                <th>Chi tiết</th>

            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
            </tr>
        </tbody>
    </table>



</div>


<?php include 'footer.php' ?>