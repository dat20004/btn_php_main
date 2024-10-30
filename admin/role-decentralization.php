<?php include 'header.php';
?>

<div class="content-wrapper subject">
    <section class="content-header">
        <h1>
            Quản lý vai trò
        </h1>

    </section>

    <!-- Main content -->

    <div class="account">
        <ul class="nav nav-tabs__account" id="myTab">
            <li class="nav-item__account">
                <button class="nav-link__account active" onclick="showTab('image')">Danh sách</button>
            </li>
        </ul>
        <hr>

        <div class="tab-content__account mt-3" id="myTabContent">
            <div id="image" class="tab-pane__account">
                <div class="mt-3">

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tên vai trò</th>
                                <th>Số tài khoản</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Admin</td>
                                <td>0</td>
                                <td><a class="btn btn-primary" href="account.php">Danh sách</a><i
                                        class="fa-solid fa-ellipsis-vertical"></i></td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>