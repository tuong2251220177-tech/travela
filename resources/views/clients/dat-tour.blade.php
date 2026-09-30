@php
    $available = max(0, (int) ($tour->maxGuests ?? 30));
    $initialAdults = min(max(1, (int) request('numAdults', 1)), max(1, $available));
    $initialChildren = min(max(0, (int) request('numChildren', 0)), max(0, $available - $initialAdults));
@endphp
@include('clients.blocks.dau-trang-giao-dien', ['pageClass' => 'booking-page pastel-booking'])
<main id="main" class="shell checkout-main">
<nav class="page-breadcrumb" aria-label="Đường dẫn"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><a href="{{ route('tour-detail', ['id' => $tour->tourId]) }}">Chi tiết tour</a><span>/</span><span>Đặt tour</span></nav>
<div class="checkout-heading"><span class="eyebrow">CHỈ CÒN MỘT BƯỚC ĐẾN HÀNH TRÌNH MỚI</span><h1>Hoàn Tất Chuyến Đi Của Bạn</h1><p>Điền thông tin hành khách và chọn phương thức thanh toán phù hợp.</p></div>
<ol class="checkout-steps" aria-label="Tiến trình đặt tour"><li class="done"><span>✓</span> Chọn tour</li><li aria-current="step"><span>2</span> Thông tin & thanh toán</li><li><span>3</span> Xác nhận đặt tour</li></ol>

<section class="checkout-content" aria-label="Thông tin đặt tour">
    {{-- <h1 class="text-center booking-header">Tổng Quan Về Chuyến Đi</h1> --}}

    <form action="{{ route('create-booking') }}" method="post" class="booking-container" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="bookingToken" value="{{ old('bookingToken', (string) \Illuminate\Support\Str::uuid()) }}">
        <input type="hidden" name="couponCode" id="applied-coupon" value="">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        <!-- Passenger Information -->
        <div class="booking-info">
            <section class="checkout-card"><h2 class="booking-header"><i class="far fa-id-card" aria-hidden="true"></i> Thông tin hành khách & người đại diện</h2><p class="section-caption">Thông tin người lớn đầu tiên được dùng để liên hệ về đơn đặt tour.</p>
            <div class="booking__infor" id="passenger-information">
                <div class="passenger-card" data-passenger-index="0" data-passenger-type="adult">
                    <h3>Người lớn 1</h3>
                    <div class="form-group">
                        <label for="username">Họ và tên*</label>
                        <input type="text" id="username" placeholder="Nhập Họ và tên" name="fullName" required>
                        <span class="error-message" id="usernameError"></span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" placeholder="sample@gmail.com" name="email" required>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label for="tel">Số điện thoại*</label>
                        <input type="tel" autocomplete="tel" id="tel" placeholder="Nhập số điện thoại liên hệ" name="tel" required>
                        <span class="error-message" id="telError"></span>
                    </div>

                    <div class="form-group">
                        <label for="address">Địa chỉ*</label>
                        <input type="text" id="address" placeholder="Nhập địa chỉ liên hệ" name="address" required>
                        <span class="error-message" id="addressError"></span>
                    </div>
                </div>
            </div>

            <template id="passenger-information-template">
                <div class="passenger-card" data-passenger-index="__INDEX__" data-passenger-type="__TYPE__">
                    <h3>__TYPE_LABEL__ __TYPE_NUMBER__</h3>
                    <div class="form-group">
                        <label for="passenger-full-name-__INDEX__">Họ và tên*</label>
                        <input type="text" id="passenger-full-name-__INDEX__" placeholder="Nhập Họ và tên"
                            name="passengers[__INDEX__][fullName]" required>
                    </div>
                    <div class="form-group">
                        <label for="passenger-email-__INDEX__">Email*</label>
                        <input type="email" id="passenger-email-__INDEX__" placeholder="sample@gmail.com"
                            name="passengers[__INDEX__][email]" required>
                    </div>
                    <div class="form-group">
                        <label for="passenger-tel-__INDEX__">Số điện thoại*</label>
                        <input type="tel" id="passenger-tel-__INDEX__" placeholder="Nhập số điện thoại liên hệ"
                            name="passengers[__INDEX__][tel]" required>
                    </div>
                    <div class="form-group">
                        <label for="passenger-address-__INDEX__">Địa chỉ*</label>
                        <input type="text" id="passenger-address-__INDEX__" placeholder="Nhập địa chỉ liên hệ"
                            name="passengers[__INDEX__][address]" required>
                    </div>
                </div>
            </template>


            <!-- Passenger Details -->
            </section>
            <section class="checkout-card">
                <h2 class="booking-header"><i class="fas fa-bus" aria-hidden="true"></i> Phương tiện di chuyển</h2>
                <p class="section-caption">Chọn xe cho cả nhóm. Phụ thu được cập nhật ngay trong tổng tiền.</p>
                <select id="transport-option" name="transportOptionId" hidden aria-hidden="true" tabindex="-1">
                    <option value="0" data-fee="0" data-unit="person">Phương tiện tiêu chuẩn — Đã gồm trong giá tour</option>
                    @foreach(($transportOptions ?? collect()) as $option)
                    <option value="{{ $option->id }}" data-fee="{{ $option->surcharge }}" data-unit="{{ $option->chargeUnit }}">{{ $option->name }} — +{{ number_format($option->surcharge, 0, ',', '.') }}đ/{{ $option->chargeUnit === 'person' ? 'khách' : 'đơn' }}</option>
                    @endforeach
                </select>
                <fieldset class="transport-choices" aria-describedby="transport-help">
                    <legend>Chọn phương tiện</legend>
                    <label class="transport-choice">
                        <input type="radio" name="transport-choice" value="0" checked>
                        <span class="transport-choice-body">
                            <span class="transport-choice-check" aria-hidden="true">✓</span>
                            <img src="{{ asset('clients/assets/images/phuong-tien/xe-tieu-chuan.jpg') }}" alt="Xe tiêu chuẩn màu trắng — ảnh minh họa" width="602" height="451" loading="lazy">
                            <span class="transport-choice-info"><strong>Xe tiêu chuẩn</strong><span class="transport-choice-price">Đã gồm trong giá tour</span><small>Ảnh minh họa</small></span>
                        </span>
                    </label>
                    @foreach(($transportOptions ?? collect()) as $option)
                    <label class="transport-choice">
                        <input type="radio" name="transport-choice" value="{{ $option->id }}">
                        <span class="transport-choice-body">
                            <span class="transport-choice-check" aria-hidden="true">✓</span>
                            @if(str_contains(mb_strtolower($option->name), 'limousine'))
                            <img src="{{ asset('clients/assets/images/phuong-tien/xe-limousine.jpg') }}" alt="{{ $option->name }} — ảnh minh họa" width="940" height="788" loading="lazy">
                            @else
                            <span class="transport-choice-placeholder"><i class="fas fa-bus" aria-hidden="true"></i></span>
                            @endif
                            <span class="transport-choice-info"><strong>{{ $option->name }}</strong><span class="transport-choice-price">+{{ number_format($option->surcharge, 0, ',', '.') }}đ<small>/{{ $option->chargeUnit === 'person' ? 'khách' : 'đơn' }}</small></span><small>Phụ thu ngoài giá tour</small></span>
                        </span>
                    </label>
                    @endforeach
                </fieldset>
                <p id="transport-help" class="section-caption">Phụ thu theo khách tính cho cả người lớn và trẻ em. Các đoàn được xếp theo loại phương tiện đã chọn. Mã giảm giá chỉ áp dụng trên giá tour, không giảm phụ thu phương tiện.</p>
            </section>
            <section class="checkout-card"><h2 class="booking-header"><i class="fas fa-user-friends" aria-hidden="true"></i> Số lượng hành khách & danh sách đoàn</h2><p class="section-caption">Chọn số khách. Với đoàn từ 3 người, hãy tải mẫu Excel và gửi lại danh sách đã điền.</p>

            <div class="booking__quantity">
                <div class="form-group quantity-selector">
                    <label for="numAdults">Người lớn</label>
                    <div class="input__quanlity">
                        <button type="button" class="quantity-btn">-</button>
                        <input type="number" class="quantity-input" value="{{ $initialAdults }}" min="1" id="numAdults"
                            name="numAdults" data-price-adults="{{ $tour->priceAdult }}" readonly>
                        <button type="button" class="quantity-btn">+</button>
                    </div>
                </div>

                <div class="form-group quantity-selector">
                    <label for="numChildren">Trẻ em</label>
                    <div class="input__quanlity">
                        <button type="button" class="quantity-btn">-</button>
                        <input type="number" class="quantity-input" value="{{ $initialChildren }}" min="0" id="numChildren"
                            name="numChildren" data-price-children="{{ $tour->priceChild }}" readonly>
                        <button type="button" class="quantity-btn">+</button>
                    </div>
                </div>
            </div>
                <div id="passenger-excel-import" class="passenger-excel-import" data-preview-url="{{ route('booking.passenger-preview') }}" data-template-url="{{ route('booking.passenger-template') }}" style="display:none;">
                    <strong>Đặt từ 3 hành khách</strong>
                    <p>Tải mẫu .xlsx, giữ đúng 1 sheet và 6 cột theo mẫu, không đổi tiêu đề, STT hoặc loại khách. Điền đủ số khách đã chọn rồi lưu dạng .xlsx hoặc CSV UTF-8 (tối đa 2 MB). Trẻ em có thể dùng email, số điện thoại và địa chỉ của người giám hộ.</p>
                    <a id="download-passenger-template" class="booking-btn" href="#" download>Tải mẫu Excel</a>
                    <label for="passenger-file">File Excel đã điền</label>
                    <p id="passenger-file-help"><strong>Chỉ nhận 2 loại file: Excel (.xlsx) và CSV UTF-8 (.csv).</strong> Dung lượng tối đa 2 MB.</p>
                    <input type="file" id="passenger-file" name="passenger_file" accept=".xlsx,.csv" aria-describedby="passenger-file-help passenger-file-status" />
                    <div id="passenger-file-preview" style="overflow-x:auto" aria-live="polite"></div>
                    <small id="passenger-file-status" role="status"></small>
                </div>


</section><div class="checkout-help"><i class="fas fa-headset" aria-hidden="true"></i><div><strong>Cần hỗ trợ đặt tour?</strong><p>Kiểm tra kỹ thông tin trước khi xác nhận. <a href="{{ route('contact') }}">Liên hệ Travela</a></p></div></div></div>

        <!-- Order Summary -->
        <div class="booking-summary">
            <h2 class="summary-heading">Tóm tắt hành trình</h2>
            @php
                $summaryImage = data_get($tour->images, '0') ?: 'clients/assets/images/widgets/tour1.jpg';
                $summaryImageUrl = str_contains($summaryImage, '/') ? asset($summaryImage) : asset('admin/assets/images/gallery-tours/' . $summaryImage);
            @endphp
            <img class="checkout-tour-image" src="{{ $summaryImageUrl }}" alt="{{ $tour->title }}" width="480" height="260">

            <div class="summary-section">
                <div>

                    <p>Mã tour : {{ $tour->tourId }}</p>
                    <input type="hidden" name="tourId" id="tourId" value="{{ $tour->tourId }}">
                    <h5 class="widget-title">{{ $tour->title }}</h5>
                    <p>Ngày khởi hành : {{ date('d-m-Y', strtotime($tour->startDate)) }}</p>
                    <p>Ngày kết thúc : {{ date('d-m-Y', strtotime($tour->endDate)) }}</p>
                    <p class="quantityAvailable">Tối đa {{ $tour->maxGuests }} khách / đoàn. Đoàn đầy sẽ mở đoàn tiếp theo.</p>
                </div>

                <div class="order-summary" aria-live="polite">
                    <div class="summary-item">
                        <span>Người lớn:</span>
                        <div>
                            <span class="quantity__adults">1</span>
                            <span>X</span>
                            <span class="total-price">0 VNĐ</span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <span>Trẻ em:</span>
                        <div>
                            <span class="quantity__children">0</span>
                            <span>X</span>
                            <span class="total-price">0 VNĐ</span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <span>Giảm giá:</span>
                        <div>
                            <span class="total-price">0 VNĐ</span>
                        </div>
                    </div>
                    <div class="summary-item"><span>Phụ thu phương tiện:</span><span id="transport-total">0 VNĐ</span></div>
                    <div class="summary-item total-price">
                        <span>Tổng cộng:</span>
                        <span>0 VNĐ</span>
                        <input type="hidden" class="totalPrice" name="totalPrice" value="">
                    </div>
                    <div class="deposit-notice" id="payment-terms">
                        Cọc trước 20%: <strong class="deposit-price">0 VNĐ</strong><br>
                        Hạn cọc: trong vòng 48 giờ sau khi đặt tour. Đơn chưa thanh toán quá hạn sẽ bị hủy. Nếu đã chuyển khoản, báo cho nhân viên trong chi tiết đơn để đối chiếu.
                    </div>
                </div>
                <div class="order-coupon" data-url="{{ route('booking.coupon-preview') }}" data-clear-url="{{ route('booking.coupon-clear') }}">
                    <input type="text" aria-label="Mã giảm giá" placeholder="Mã giảm giá" style="width: 65%;">
                    <button type="button" style="width: 30%" class="booking-btn btn-coupon">Áp dụng</button>
                </div>

                <div id="paypal-button-container"></div>
                @include('clients.blocks.goi-y-ma-giam-gia')

            <!-- Payment Method -->
            <h2 class="booking-header">Phương thức thanh toán</h2>
            <input type="hidden" id="payment-plan" name="paymentPlan" value="deposit">
            <fieldset class="payment-plan-choices">
                <legend>Bạn muốn thanh toán bao nhiêu?</legend>
                <label class="payment-plan-choice">
                    <input type="radio" name="payment-plan-choice" value="deposit" checked>
                    <span class="payment-plan-card">
                        <span class="payment-plan-heading"><strong>Đặt cọc 20%</strong><span class="payment-plan-check" aria-hidden="true">✓</span></span>
                        <span class="payment-plan-price" id="payment-deposit-price">0 VNĐ</span>
                        <span class="payment-plan-description">Trả trước để giữ chỗ, thanh toán phần còn lại sau.</span>
                    </span>
                </label>
                <label class="payment-plan-choice">
                    <input type="radio" name="payment-plan-choice" value="full">
                    <span class="payment-plan-card">
                        <span class="payment-plan-heading"><strong>Thanh toán toàn bộ</strong><span class="payment-plan-check" aria-hidden="true">✓</span></span>
                        <span class="payment-plan-price" id="payment-full-price">0 VNĐ</span>
                        <span class="payment-plan-description">Trả 100% giá trị đơn trong một lần.</span>
                    </span>
                </label>
            </fieldset>
            <p class="payment-plan-due"><span>Thanh toán lần này</span><strong id="payment-now-amount">0 VNĐ</strong></p>
            <p><small>Trạng thái thanh toán được cập nhật sau khi nhân viên đối chiếu tiền thực nhận.</small></p>

            <label class="payment-option">
                <input type="radio" name="payment" value="office-payment" required>
                <img src="{{ asset('clients/assets/images/contact/icon.png') }}" alt="Office Payment">
                Thanh toán tại văn phòng
            </label>

            <label class="payment-option">
                <input type="radio" name="payment" value="bank-transfer" required>
                <i class="fas fa-qrcode bank-payment-icon" aria-hidden="true"></i>
                Chuyển khoản ngân hàng / Quét VietQR
            </label>





            <div id="bank-transfer-info" class="bank-transfer-box" style="display:none; margin-top:18px;">
                <h3>Thanh toán QR sau khi giữ chỗ</h3>
                <p>Bấm “Xác nhận đặt tour” để kiểm tra chỗ và tạo đơn. Mã QR với số tiền chính xác và nội dung riêng cho đơn sẽ xuất hiện ở trang tiếp theo.</p>
                <p>Chưa chuyển tiền ở bước này. Sau khi chuyển khoản, bấm “Tôi đã chuyển khoản” để nhân viên đối chiếu.</p>
            </div>
            <input type="hidden" name="payment_hidden" id="payment_hidden">
            <!-- Privacy Agreement Section -->
            <div class="privacy-section">
                <p>Bằng cách xác nhận đặt tour, khách hàng đồng ý rằng các điều kiện điều khoản
                    này sẽ được áp dụng. Vui lòng đọc kỹ điều kiện điều khoản trước khi lựa chọn sử dụng dịch vụ của
                    Travela.</p>
                <div class="privacy-checkbox">
                    <input type="checkbox" id="agree" name="agree" required>
                    <label for="agree">Tôi đã đọc và đồng ý với <a href="#payment-terms">Điều khoản thanh
                            toán</a></label>
                </div>
            </div>

                <button type="submit" class="booking-btn btn-submit-booking">Xác nhận đặt tour <span aria-hidden="true">↗</span></button>

                <button type="button" id="btn-momo-payment" class="booking-btn" style="display: none;"
                    data-urlmomo = "{{ route('createMomoPayment') }}">Thanh toán với Momo <img src="{{ asset('clients/assets/images/booking/icon-thanh-toan-momo.png') }}" alt="" style="width: 10%"></button>

            </div>
        </div>
    </form>
</section>


</main>
@include('clients.blocks.cuoi-trang-giao-dien', ['bookingScripts' => true])
