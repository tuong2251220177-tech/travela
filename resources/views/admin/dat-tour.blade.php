@include('admin.blocks.dau-trang')
<div class="container body">
    <div class="main_container">
        @include('admin.blocks.thanh-ben')

        <!-- page content -->
        <div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc')
            <div class="">
                <div class="page-title">
                    <div class="title_left">
                        <h3>Quản lý <small>Booking</small></h3>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="row">
                    <div class="col-md-12 col-sm-12 ">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Danh sách đặt tour</h2>
                                <ul class="nav navbar-right panel_toolbox">
                                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                    </li>
                                    <li><a class="close-link"><i class="fa fa-close"></i></a>
                                    </li>
                                </ul>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card-box booking-table-panel">
                                            <p class="text-muted font-13 m-b-30">
                                                Theo dõi đơn đặt, kiểm tra thanh toán và xử lý yêu cầu của khách hàng.
                                                Nhấn dấu <strong>+</strong> ở tên tour để xem thông tin bổ sung.
                                            </p>
                                            <table id="datatable-booking" class="table table-striped table-bordered"
                                                style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th data-priority="1" class="booking-tour">Tour / đoàn</th>
                                                        <th data-priority="3" class="booking-customer">Khách hàng</th>
                                                        <th class="none">Email</th>
                                                        <th class="none">Số điện thoại</th>
                                                        <th class="none">Địa chỉ</th>
                                                        <th class="none">Ngày đặt</th>
                                                        <th class="none">Người lớn</th>
                                                        <th class="none">Trẻ em</th>
                                                        <th data-priority="4" class="booking-money">Tổng tiền (₫)</th>
                                                        <th class="none">Tiền cọc</th>
                                                        <th data-priority="5" class="booking-status">Trạng thái đơn</th>
                                                        <th class="none">Phương thức thanh toán</th>
                                                        <th class="none">Tình trạng thanh toán</th>
                                                        <th data-priority="2" class="booking-actions">Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="tbody-booking">
                                                    @include('admin.partials.danh-sach-dat-tour')
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page content -->
    </div>
</div>
@include('admin.blocks.chan-trang')
