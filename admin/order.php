<?php include 'header.php';
$sql = "SELECT o.*, SUM(od.quantity) as quantity, SUM(od.price*od.quantity) as total from orders o JOIN order_detail od ON od.order_id  = o.id group by o.id order by id desc";
if(isset($_GET['status'])){
    $status = $_GET['status'];
    $sql = "SELECT o.*, SUM(od.quantity) as quantity, SUM(od.price*od.quantity) as total from orders o JOIN order_detail od ON od.order_id  = o.id where status = $status group by o.id order by id desc";
}
$query = $conn->query($sql);
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Quản lý đơn hàng
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Ngày đặt</th>
                            <th>Họ tên</th>
                            <th>Số điện thoại</th>
                            <th>Tổng số lượng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $n = 1; while($od = $query->fetch_object()):?>
                        <tr>
                            <td>
                                <?php echo $n; ?>
                            </td>
                            <td>
                                <?php echo $od->order_date; ?>
                            </td>
                            <td>
                                <?php echo $od->name;?></td>
                            <td>
                            </td>
                            <td><?php echo $od->phone;?></td>
                            <td><?php echo $od->quantity;?></td>
                            <td><?php echo number_format($od->total)?> vnđ</td>
                            <td><?php if ($od->status == 0) : ?>
                                <label class="label label-info"> <strong>Trạng thái: Đơn hàng mới!</strong></label>

                                <?php elseif ($od->status == 1) : ?>
                                <label class="label label-warning"> <strong>Trạng thái: Đơn hàng đang
                                        giao!</strong></label>

                                <?php elseif ($od->status == 2) : ?>
                                <label class="label label-info"> <strong>Trạng thái: Đã xong!</strong></label>

                                <?php else : ?>
                                <label class="label label-success"> <strong>Trạng thái: Đã hủy!</strong></label>


                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="order-detail.php?id=<?php echo $od->id;?>" class="btn btn-sm btn-danger">Chi
                                    tiết</a>
                            </td>
                        </tr>
                        <?php $n++; endwhile;?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php' ?>