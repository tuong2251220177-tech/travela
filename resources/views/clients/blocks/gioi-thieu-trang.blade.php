@php
    $pageCopy = [
        'tours' => ['Khám Phá Hành Trình Mới', 'Từ miền núi yên bình đến biển xanh rực rỡ, tìm chuyến đi dành riêng cho bạn.'],
        'destination' => ['Việt Nam, Muôn Vẻ Đẹp', 'Mỗi miền đất là một câu chuyện. Bạn muốn bắt đầu từ đâu?'],
        'about' => ['Cùng Bạn Đi Xa Hơn', 'Travela kết nối bạn với những điểm đến và những trải nghiệm đáng nhớ.'],
        'contact' => ['Kết Nối Với Travela', 'Chia sẻ mong muốn của bạn, chúng tôi sẽ cùng bạn chuẩn bị hành trình.'],
        'team' => ['Những Người Bạn Đồng Hành', 'Gặp gỡ đội ngũ hướng dẫn viên và chuẩn bị cho một chuyến đi trọn vẹn.'],
        'user-profile' => ['Góc Nhỏ Của Bạn', 'Cập nhật thông tin cá nhân để sẵn sàng cho những hành trình tiếp theo.'],
        'my-tours' => ['Những Chuyến Đi Của Bạn', 'Theo dõi lịch trình, trạng thái đặt tour và những kỷ niệm đã qua.'],
        'tour-booked' => ['Chi Tiết Chuyến Đi', 'Thông tin hành khách, lịch trình và thanh toán trong cùng một nơi.'],
    ];
    $copy = $pageCopy[request()->route()?->getName()] ?? [$title ?? 'Khám phá Travela', 'Tìm cảm hứng cho hành trình tiếp theo của bạn.'];
@endphp
<section class="site-page-intro"><div class="shell"><nav aria-label="Đường dẫn"><a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>{{ $title ?? 'Travela' }}</span></nav><span class="eyebrow">TRAVEL WITH INSPIRATION</span><h1>{{ $copy[0] }}</h1><p>{{ $copy[1] }}</p><span class="intro-spark" aria-hidden="true">✦</span></div></section>
