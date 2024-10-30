<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Quản lý đơn hàng COD
        </h1>

    </section>

    <!-- Main content -->

    <div class="account">
        <ul class="nav nav-tabs__account" id="myTab">
            <li class="nav-item__account">
                <button class="nav-link__account active" onclick="showTab('image')">Danh sách</button>
            </li>
            <li class="nav-item__account">
                <button class="nav-link__account" onclick="showTab('search')">Tìm kiếm</button>
            </li>
            <li class="nav-item__account">
                <button class="nav-link__account" onclick="showTab('edit')">Phân tỉ lệ xử lý</button>
            </li>
            <li class="nav-item__account">
                <button class="nav-link__account" onclick="showTab('seller')">Thống kê seller</button>
            </li>
        </ul>
        <hr>

        <div class="tab-content__account mt-3" id="myTabContent">
            <div id="image" class="tab-pane__account">
                <div class="mt-3">
                    <div class="account-search">
                        <div class="account-search__right">
                            <form action="" method="POST" role="form">
                                <select name="" id="input" required="required">
                                    <option value="">Sắp xếp mặc định</option>
                                    <option value="">Sắp xếp mặc định</option>
                                    <option value="">Sắp xếp mặc định</option>
                                    <option value="">Sắp xếp mặc định</option>
                                </select>
                                <select name="" id="input" required="required">
                                    <option value="">Nhân viên xử lý</option>
                                </select>
                                <select name="" id="input" required="required">
                                    <option value="">Toàn bộ thời gian</option>
                                </select>
                            </form>
                        </div>
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

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

            <div id="search" class="tab-pane__account hidden">
                <div class="mt-3">
                    <div class="input-group">
                        <input type="text" name="" placeholder="Mã đơn hàng/Email" id="">
                        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                    <hr>
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

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="edit" class="tab-pane__account hidden">
                <div class="mt-3">
                    <hr>
                    <h2>Danh sách nhân viên xử lý đơn</h2>
                    <hr>
                    <table class="table table-hover">
                        <thead>
                            <tr>

                                <th>STT</th>
                                <th>Nhân viên </th>
                                <th>Nhận đơn</th>
                                <th>Hệ số nhận đơn</th>
                                <th>Cài đặt</th>


                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="seller" class="tab-pane__account hidden">
                <div class="mt-3">
                    <h2>Tỉ lệ xử lý đơn hàng</h2>
                    <hr>
                    <div class="account-search">
                        <div class="account-search__right">
                            <form action="" method="POST" role="form">
                                <select name="" id="input" required="required">
                                    <option value="">Nhân viên xử lý</option>
                                </select>
                                <select name="" id="input" required="required">
                                    <option value="">Toàn bộ thời gian</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Seller</th>
                                <th>Số đơn</th>
                                <th>Thành công</th>
                                <th>Tỉ lệ</th>
                                <th>Sai số</th>
                                <th>Chưa gọi</th>
                                <th>Số đơn</th>
                                <th>Từ chối</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>