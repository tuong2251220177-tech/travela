@include('admin.blocks.dau-trang')

<div class="container body">
    <div class="main_container">
        @include('admin.blocks.thanh-ben')


        <!-- page content -->
        <div class="right_col" role="main">
@include('admin.blocks.tieu-de-khu-vuc', ['title' => 'Tổng quan'])
            <section class="admin-summary" aria-label="Tổng quan hoạt động">
                <article class="admin-stat">
                    <div class="admin-stat-top"><span>Tour đang hoạt động</span><i class="fa fa-map-o" aria-hidden="true"></i></div>
                    <strong class="admin-stat-value">{{ number_format($summary['tourWorking'], 0, ',', '.') }}</strong>
                    <a href="{{ route('admin.tours') }}">Xem danh sách tour &rarr;</a>
                </article>
                <article class="admin-stat">
                    <div class="admin-stat-top"><span>Đơn đặt tour</span><i class="fa fa-calendar-check-o" aria-hidden="true"></i></div>
                    <strong class="admin-stat-value">{{ number_format($summary['countBooking'], 0, ',', '.') }}</strong>
                    <a href="{{ route('admin.booking') }}">Quản lý đơn đặt &rarr;</a>
                </article>
                <article class="admin-stat">
                    <div class="admin-stat-top"><span>Người dùng</span><i class="fa fa-users" aria-hidden="true"></i></div>
                    <strong class="admin-stat-value">{{ number_format($summary['userCount'], 0, ',', '.') }}</strong>
                    <a href="{{ route('admin.users') }}">Xem khách hàng &rarr;</a>
                </article>
                <article class="admin-stat">
                    <div class="admin-stat-top"><span>Tổng doanh thu</span><i class="fa fa-credit-card" aria-hidden="true"></i></div>
                    <strong class="admin-stat-value">{{ number_format($summary['totalAmount'], 0, ',', '.') }} <small>&#8363;</small></strong>
                    <span>Số liệu từ hệ thống</span>
                </article>
            </section>
            <nav class="admin-shortcuts" aria-label="Thao tác nhanh">
                <span>Thao tác nhanh</span>
                <a href="{{ route('admin.page-add-tours') }}"><i class="fa fa-plus" aria-hidden="true"></i> Thêm tour mới</a>
                <a href="{{ route('admin.booking') }}"><i class="fa fa-list-alt" aria-hidden="true"></i> Quản lý đơn đặt</a>
                <a href="{{ route('admin.guide-assignment') }}"><i class="fa fa-user-plus" aria-hidden="true"></i> Phân công hướng dẫn viên</a>
            </nav>

            <div class="row">
                <div class="col-md-6 col-sm-4 ">
                    <div class="x_panel tile fixed_height_320 overflow_hidden">
                        <div class="x_title">
                            <h2>Điểm đến </h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                        aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <table class="" style="width:100%">
                                <tr>
                                    <th style="width:37%;">
                                        <p>Tổng hợp danh sách tours</p>
                                    </th>
                                    <th>
                                        <div class="col-lg-7 col-md-7 col-sm-7 ">
                                            <p class="">Tên</p>
                                        </div>
                                        <div class="col-lg-5 col-md-5 col-sm-5 " style="text-align: center">
                                            <p class="">Số lượng</p>
                                        </div>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <canvas class="canvasDoughnut" height="140" width="140"
                                            style="margin: 15px 10px 10px 0"
                                            data-chart-values="{{ json_encode($dataDomain['values']) }}"></canvas>
                                    </td>
                                    <td>
                                        <table class="tile_info">
                                            <tr>
                                                <td>
                                                    <p><i class="fa fa-square red"></i>Miền Bắc </p>
                                                </td>
                                                <td>{{ $dataDomain['values'][0] }}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p><i class="fa fa-square green"></i>Miền Trung </p>
                                                </td>
                                                <td>{{ $dataDomain['values'][1] }}</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <p><i class="fa fa-square purple"></i>Miền Nam </p>
                                                </td>
                                                <td>{{ $dataDomain['values'][2] }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-4  ">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Đặt tour</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                        aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">

                            <div id="echart_donut" data-payment-method='{{ json_encode($paymentStatus) }}'
                                style="height: 350px; -webkit-tap-highlight-color: transparent; user-select: none; position: relative; background-color: transparent;"
                                _echarts_instance_="ec_1733563825119">
                                <div
                                    style="position: relative; overflow: hidden; width: 380px; height: 350px; cursor: default;">
                                    <canvas width="380" height="350" data-zr-dom-id="zr_0"
                                        style="position: absolute; left: 0px; top: 0px; width: 380px; height: 350px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0);"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-6  ">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Tours <small>được đặt nhiều nhất</small></h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên</th>
                                        <th>Khách đã đăng ký</th>
                                        <th>Số đoàn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($toursBooked as $item)
                                        <tr>
                                            <th scope="row">{{ $item->tourId }}</th>
                                            <td>{{ $item->title }}</td>
                                            <td>{{ $item->booked_quantity }}</td>
                                            <td>{{ $item->group_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6  ">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Đơn đặt mới</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ và tên</th>
                                        <th>Tên tours</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($newBooking as $item)
                                        <tr>
                                            <th scope="row">
                                                <a href="{{ route('admin.booking-detail',['id' => $item->bookingId]) }}">{{ $item->bookingId }}</a>
                                            </th>
                                            <td>{{ $item->fullName }}</td>
                                            <td>{{ $item->tour_name }}</td>
                                            <td>{{ number_format($item->totalPrice, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge badge-warning">Chưa xác nhận</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 ">

                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Doanh thu theo tháng</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                        aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <canvas id="lineChart" data-revenue-per-month = {{ json_encode($revenue)}}></canvas>
                        </div>

                    </div>

                    <div class="clearfix"></div>
                </div>

            </div>
        </div>
        <!-- /page content -->
    </div>
</div>

@include('admin.blocks.chan-trang')
