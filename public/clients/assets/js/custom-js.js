$(document).ready(function () {

    var sqlInjectionPattern = /[<>'"%;()&+]/;

    /****************************************
     *              PAGE LOGIN              *
     * ***************************************/
    //Handle click switch tab
    $("#sign-up").click(function () {
        $(".sign-in").hide();
        $(".signup").show();
    });
    $("#sign-in").click(function () {
        $(".signup").hide();
        $(".sign-in").show();
    });

    // Handle form submission for signin
    $("#login-form").on("submit", function (e) {
        e.preventDefault();
        var userName = $("#username_login").val().trim();
        var password = $("#password_login").val().trim();

        // Đặt lại nội dung thông báo lỗi và ẩn chúng
        $("#validate_username").hide().text("");
        $("#validate_password").hide().text("");

        var isValid = true;

        // Kiểm tra độ dài mật khẩu
        if (password.length < 6) {
            isValid = false;
            $("#validate_password")
                .show()
                .text("Mật khẩu phải có ít nhất 6 ký tự.");
        }

        // Kiểm tra tên đăng nhập và mật khẩu không chứa ký tự đặc biệt (SQL injection)
        
        if (sqlInjectionPattern.test(userName)) {
            isValid = false;
            $("#validate_username")
                .show()
                .text("Tên đăng nhập không được chứa ký tự đặc biệt.");
        }

        if (sqlInjectionPattern.test(password)) {
            isValid = false;
            $("#validate_password")
                .show()
                .text("Mật khẩu không được chứa ký tự đặc biệt.");
        }

        if (isValid) {
            var formData = {
                username: userName,
                password: password,
                _token: $('input[name="_token"]').val(),
            };
            console.log(formData, $(this).attr("action"));

            $.ajax({
                type: "POST",
                url: $(this).attr("action"),
                data: formData,
                success: function (response) {
                    if (response.success) {
                        window.location.href = "/";
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    var message = "Có lỗi xảy ra. Vui lòng thử lại sau.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    toastr.error(message);
                    $("#register-form")
                        .removeClass("hidden-content");
                    $(".loader").hide();
                },
            });
        }
    });

    // Handle form submission for signup
    $("#register-form").on("submit", function (e) {
        e.preventDefault();
        $(".loader").show();
        $("#register-form").addClass("hidden-content");

        // Lấy giá trị của các trường nhập liệu
        var userName = $("#username_register").val().trim();
        var email = $("#email_register").val().trim();
        var password = $("#password_register").val().trim();
        var rePass = $("#re_pass").val().trim();

        // Đặt lại nội dung thông báo lỗi và ẩn chúng
        $("#validate_username_regis").hide().text("");
        $("#validate_email_regis").hide().text("");
        $("#validate_password_regis").hide().text("");
        $("#validate_repass").hide().text("");

        // Kiểm tra lỗi
        var isValid = true;

        // Kiểm tra tên đăng nhập không chứa ký tự SQL injection
        if (sqlInjectionPattern.test(userName)) {
            isValid = false;
            $("#validate_username_regis")
                .show()
                .text("Tên tài khoản không được chứa ký tự đặc biệt.");
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            isValid = false;
            $("#validate_email_regis").show().text("Email không hợp lệ.");
        }

        if (password.length < 6) {
            isValid = false;
            $("#validate_password_regis")
                .show()
                .text("Mật khẩu phải có ít nhất 6 ký tự.");
        }

        if (sqlInjectionPattern.test(password)) {
            isValid = false;
            $("#validate_password_regis")
                .show()
                .text("Mật khẩu không được chứa ký tự đặc biệt.");
        }

        // Kiểm tra nhập lại mật khẩu
        if (password !== rePass) {
            isValid = false;
            $("#validate_repass").show().text("Mật khẩu nhập lại không khớp.");
        }

        if (isValid) {
            var formData = {
                username_register: userName,
                email_register: email,
                password_register: password,
                _token: $('input[name="_token"]').val(),
            };
            console.log(formData, $(this).attr("action"));

            $.ajax({
                type: "POST",
                url: $(this).attr("action"),
                data: formData,
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message, { timeOut: 5000 });
                    } else {
                        toastr.error(response.message);
                    }
                    $("#register-form")
                        .removeClass("hidden-content")
                        .trigger("reset");
                    $(".loader").hide();
                },
                error: function (xhr, textStatus, errorThrown) {
                    toastr.error("Có lỗi xảy ra. Vui lòng thử lại sau.");
                    $("#register-form").removeClass("hidden-content");
                    $(".loader").hide();
                },
            });
        } else {
            $("#register-form").removeClass("hidden-content");
            $(".loader").hide();
        }
    });

    /****************************************
     *              HOME PAGE             *
     * ***************************************/
    $("#start_date, #end_date").datetimepicker({
        format: "d/m/Y",
        timepicker: false,
    });
    /****************************************
     *              HEADER                  *
     * ***************************************/
    $("#userDropdown").click(function () {
        $("#dropdownMenu").toggle(); // Toggle dropdown menu when user clicks
    });

    /****************************************
     *              PAGE TOURS              *
     * ***************************************/
    // Kiểm tra nếu thanh trượt đã tồn tại
    if ($(".price-slider-range").length) {
        $(".price-slider-range").on("slide", function (event, ui) {
            filterTours(ui.values[0], ui.values[1]);
        });
    }
    $('input[name="domain"], input[name="filter_star"], input[name="duration"]').on("change", function () {
        filterTours();
    });

    $("#sorting_tours").on("change", function () {
        filterTours(null, null);
    });

    function filterTours(minPrice = null, maxPrice = null) {
        $(".loader").show();
        $("#tours-container").addClass("hidden-content");

        if (minPrice === null || maxPrice === null) {
            minPrice = $(".price-slider-range").slider("values", 0);
            maxPrice = $(".price-slider-range").slider("values", 1);
        }

        var domain = $('input[name="domain"]:checked').val();
        var star = $('input[name="filter_star"]:checked').val();
        var duration = $('input[name="duration"]:checked').val();
        var sorting = $("#sorting_tours").val();

        formDataFilter = {
            minPrice: minPrice,
            maxPrice: maxPrice,
            domain: domain,
            star: star,
            time: duration,
            sorting: sorting,
        };
        console.log(formDataFilter);

        $.ajax({
            url: filterToursUrl,
            method: "GET",
            data: formDataFilter,
            success: function (res) {
                $("#tours-container").html(res).removeClass("hidden-content");
                $("#tours-container .destination-item").addClass("aos-animate");
                $(".loader").hide();
            },
            error: function () {
                toastr.error("Không tải được tour. Vui lòng thử lại.");
            },
            complete: function () {
                $("#tours-container").removeClass("hidden-content");
                $(".loader").hide();
            },
        });
    }

    $(document).on("click", ".pagination-tours a", function (e) {
        e.preventDefault();
        $(".loader").show();
        $("#tours-container").addClass("hidden-content");

        var url = $(this).attr("href");
        console.log(url);

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                // Cập nhật toàn bộ nội dung (tours và phân trang)
                $("#tours-container")
                    .html(response.tours)
                    .removeClass("hidden-content");
                $("#tours-container .destination-item").addClass("aos-animate");
                $("#tours-container .pagination-tours").addClass("aos-animate");
                $(".loader").hide();
            },
            error: function (xhr, status, error) {
                $("#tours-container").removeClass("hidden-content"); $(".loader").hide(); toastr.error("Không tải được danh sách tour. Vui lòng thử lại.");
            },
        });
    });

    // Hàm để clear các filter đã chọn
    $(".clear_filter a").on("click", function (e) {
        e.preventDefault();
        $(".loader").show();
        $("#tours-container").addClass("hidden-content");
        // Reset slider giá về giá trị mặc định (ví dụ: 0 đến 20000000)
        $(".price-slider-range").slider("values", [0, 20000000]);

        // Bỏ chọn radio và checkbox
        $('input[name="domain"]').prop("checked", false);
        $('input[name="filter_star"]').prop("checked", false);
        $('input[name="duration"]').prop("checked", false);

        
        var url = $(this).attr("href");

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                // Cập nhật toàn bộ nội dung (tours và phân trang)
                $("#tours-container")
                    .html(response.tours)
                    .removeClass("hidden-content");
                $("#tours-container .destination-item").addClass("aos-animate");
                $("#tours-container .pagination-tours").addClass("aos-animate");
                $(".loader").hide();
            },
            error: function (xhr, status, error) {
                $("#tours-container").removeClass("hidden-content"); $(".loader").hide(); toastr.error("Không tải được danh sách tour. Vui lòng thử lại.");
            },
        });
    });

    /****************************************
     *             PAGE USER-PROFILE        *
     * ***************************************/
    $(".updateUser").on("submit", function (e) {
        e.preventDefault();
        var fullName = $("#inputFullName").val();
        var address = $("#inputLocation").val();
        var email = $("#inputEmailAddress").val();
        var phone = $("#inputPhone").val();

        var dataUpdate = {
            fullName: fullName,
            address: address,
            email: email,
            phone: phone,
            _token: $('input[name="_token"]').val(),
        };

        console.log(dataUpdate);

        $.ajax({
            type: "POST",
            url: $(this).attr("action"),
            data: dataUpdate,
            success: function (response) {
                console.log(response);

                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                toastr.error("Có lỗi xảy ra. Vui lòng thử lại sau.");
            },
        });
    });

    $("#update_password_profile").click(function () {
        $("#card_change_password").toggle();
    });

    $(".change_password_profile").on("submit", function (e) {
        e.preventDefault();
        var oldPass = $("#inputOldPass").val();
        var newPass = $("#inputNewPass").val();
        var isValid = true;

        // Kiểm tra độ dài mật khẩu
        if (oldPass.length < 6 || newPass.length < 6) {
            isValid = false;
            $("#validate_password")
                .show()
                .text("Mật khẩu phải có ít nhất 6 ký tự.");
        }

        if (sqlInjectionPattern.test(newPass)) {
            isValid = false;
            $("#validate_password")
                .show()
                .text("Mật khẩu không được chứa ký tự đặc biệt.");
        }

        if (isValid) {
            $("#validate_password").hide().text("");
            var updatePass = {
                oldPass: oldPass,
                newPass: newPass,
                _token: $('input[name="_token"]').val(),
            };

            console.log(updatePass);

            $.ajax({
                type: "POST",
                url: $(this).attr("action"),
                data: updatePass,
                success: function (response) {
                    if (response.success) {
                        $("#validate_password").hide().text("");
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    $("#validate_password")
                        .show()
                        .text(xhr.responseJSON.message);
                    toastr.error(xhr.responseJSON.message);
                },
            });
        }
    });

    //Update avatar
    $("#avatar").on("change", function () {
        const file = event.target.files[0];

        if (file) {
            // Hiển thị ảnh vừa chọn trước khi gửi lên server
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#avatarPreview").attr("src", e.target.result);
                $(".img-account-profile").attr("src", e.target.result);
            };
            reader.readAsDataURL(file);
            var __token = $(this)
                .closest(".card-body")
                .find("input.__token")
                .val();
            var url_avatar = $(this)
                .closest(".card-body")
                .find("input.label_avatar")
                .val();
            // Tạo FormData để gửi file qua AJAX
            const formData = new FormData();
            formData.append("avatar", file);

            console.log(url_avatar);

            // // Gửi AJAX đến server
            $.ajax({
                url: url_avatar,
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": __token,
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error("Có lỗi xảy ra. Vui lòng thử lại sau.");
                },
            });
        }
    });

    /****************************************
     *             PAGE BOOKING             *
     * ***************************************/
    let discount = 0; // Giảm giá, có thể cập nhật khi áp dụng mã giảm giá
    let couponCounts = '';
    let couponRequest = null;
    let couponLoading = false;
    let totalPrice = 0; // Khai báo biến totalPrice để lưu tổng giá trị
    let selectedTransportId = $('#transport-option').val() || '0';

    function refreshCouponButtons() {
        const activeCode = ($('#applied-coupon').val() || '').toString().trim().toUpperCase();
        const hasActive = activeCode !== '';

        $('.order-coupon input').prop('readonly', hasActive || couponLoading);
        $('.btn-coupon').prop('disabled', couponLoading).text(couponLoading ? 'Đang xử lý…' : (hasActive ? 'Hủy mã' : 'Áp dụng')).toggleClass('coupon-cancel', hasActive);

        $('.choose-coupon').each(function () {
            const code = ($(this).data('code') || '').toString().trim().toUpperCase();
            const isActive = hasActive && code === activeCode;
            const isDisabled = couponLoading || (hasActive && !isActive) || (!hasActive && $(this).data('ineligible'));
            $(this).prop('disabled', isDisabled)
                .css({ opacity: isDisabled ? 0.5 : 1, cursor: isDisabled ? 'not-allowed' : 'pointer' })
                .text(isActive ? 'Hủy mã' : (hasActive ? 'Hủy mã hiện tại để chọn' : 'Sử dụng mã này'));
        });
    }

    function clearCouponSelection({ resetInput = true, silent = false } = {}) {
        if (couponRequest) couponRequest.abort();
        discount = 0;
        $('#applied-coupon').val('');
        if (resetInput) $('.order-coupon input').val('');
        refreshCouponButtons();
        updateSummary();
        if (!silent) toastr.info('Mã giảm giá đã được hủy.');
    }

    function getQuantityAvailable() {
        const availableText = $(".quantityAvailable").text();
        const match = availableText && availableText.match(/\d+/);
        return match ? parseInt(match[0], 10) : 0;
    }

    function renderPassengerFields() {
        const adultCount = parseInt($("#numAdults").val());
        const passengerCount =
            adultCount + parseInt($("#numChildren").val());
        const passengerInformation = $("#passenger-information");
        const template = $("#passenger-information-template").html();

        while (passengerInformation.children(".passenger-card").length < passengerCount) {
            const index = passengerInformation.children(".passenger-card").length;
            const isAdult = index < adultCount;
            const type = isAdult ? "adult" : "child";
            const typeLabel = isAdult ? "Người lớn" : "Trẻ em";
            const typeNumber = isAdult ? index + 1 : index - adultCount + 1;
            passengerInformation.append(
                template
                    .replaceAll("__INDEX__", index)
                    .replaceAll("__TYPE__", type)
                    .replaceAll("__TYPE_LABEL__", typeLabel)
                    .replaceAll("__TYPE_NUMBER__", typeNumber)
            );
        }

        while (passengerInformation.children(".passenger-card").length > passengerCount) {
            passengerInformation.children(".passenger-card").last().remove();
        }

        passengerInformation.children(".passenger-card").each(function (index) {
            const isAdult = index < adultCount;
            const typeLabel = isAdult ? "Người lớn" : "Trẻ em";
            const typeNumber = isAdult ? index + 1 : index - adultCount + 1;
            $(this)
                .attr("data-passenger-type", isAdult ? "adult" : "child")
                .find("h3")
                .text(typeLabel + " " + typeNumber);
        });

        const counts = adultCount + ":" + (passengerCount - adultCount);
        if (passengerCounts !== counts) {
            resetPassengerFile();
            passengerCounts = counts;
        }
        const needsExcel = passengerCount > 2;
        passengerInformation.children(".passenger-card").slice(1).toggle(!needsExcel)
            .find("input").prop("disabled", needsExcel);
        const excelImport = $("#passenger-excel-import");
        excelImport.toggle(needsExcel);
        $("#passenger-file").prop("required", needsExcel);
        if (needsExcel) {
            const templateUrl = excelImport.data("template-url");
            $("#download-passenger-template").attr("href", templateUrl + "?adults=" + adultCount + "&children=" + (passengerCount - adultCount));
        } else {
            $("#passenger-file").val("");
            $("#passenger-file-status").text("");
        }
    }

    let passengerUpload = null;
    let passengerFileReady = false;
    let passengerCounts = "";
    function resetPassengerFile() {
        if (passengerUpload) passengerUpload.abort();
        passengerUpload = null;
        passengerFileReady = false;
        $("#passenger-file").val("");
        $("#passenger-file-preview, #passenger-file-status").empty();
    }
    $("#passenger-file").on("change", function () {
        if (passengerUpload) passengerUpload.abort();
        passengerFileReady = false;
        $("#passenger-file-preview, #passenger-file-status").empty();
        const file = this.files[0];
        if (!file) return;
        if (!/\.(xlsx|csv)$/i.test(file.name) || file.size > 2 * 1024 * 1024) {
            $("#passenger-file-status").text(file.size > 2 * 1024 * 1024 ? "File quá lớn. Vui lòng chọn file tối đa 2 MB." : "Sai định dạng: chỉ nhận Excel .xlsx hoặc CSV UTF-8. Không nhận Word, PDF, ảnh hoặc .xls. Hãy tải mẫu bên trên và điền lại.");
            this.value = "";
            return;
        }
        const data = new FormData();
        data.append("passenger_file", file);
        data.append("adults", $("#numAdults").val());
        data.append("children", $("#numChildren").val());
        $("#passenger-file-status").text("Đang kiểm tra file hành khách…");
        passengerUpload = $.ajax({
            url: $("#passenger-excel-import").data("preview-url"), type: "POST",
            headers: { "X-CSRF-TOKEN": $("form.booking-container input[name='_token']").val(), "Accept": "application/json" },
            data, contentType: false, processData: false, timeout: 30000,
            success: function (response) {
                passengerFileReady = true;
                const passengers = response.passengers;
                const first = passengers[0];
                $("#username").val(first.fullName); $("#email").val(first.email);
                $("#tel").val(first.tel); $("#address").val(first.address);
                const table = $("<table>").addClass("table");
                const header = $("<tr>").appendTo($("<thead>").appendTo(table));
                ["STT", "Họ và tên", "Email", "Số điện thoại", "Địa chỉ"].forEach(text => $("<th>").text(text).appendTo(header));
                const body = $("<tbody>").appendTo(table);
                passengers.forEach((p, i) => {
                    const row = $("<tr>").appendTo(body);
                    [i + 1, p.fullName, p.email, p.tel, p.address].forEach(text => $("<td>").text(text).appendTo(row));
                });
                $("#passenger-file-preview").empty().append(table);
                $("#passenger-file-status").text("Đã kiểm tra đủ " + passengers.length + " hành khách. Kiểm tra danh sách bên dưới trước khi đặt tour.");
            },
            error: function (xhr, status) {
                if (status === "abort") return;
                const errors = xhr.responseJSON && xhr.responseJSON.errors;
                const message = errors ? Object.values(errors).flat()[0] : (xhr.status === 413 ? "File vượt giới hạn tải lên của máy chủ. Vui lòng dùng file tối đa 2 MB." : xhr.status === 419 ? "Phiên làm việc đã hết hạn. Tải lại trang rồi chọn lại file." : status === "timeout" ? "Kiểm tra file quá thời gian. Vui lòng thử lại với file mẫu tối đa 2 MB." : "Không kiểm tra được file. Kiểm tra kết nối và chọn lại file để thử lại.");
                $("#passenger-file-status").text(message);
                $("#passenger-file").val("");
            }
        });
    });

    function updateSummary() {
        if (!$("#numAdults").length) return;
        const quantityAvailable = getQuantityAvailable();
        let numAdults = parseInt($("#numAdults").val()) || 0;
        let numChildren = parseInt($("#numChildren").val()) || 0;

        if (numAdults + numChildren > quantityAvailable && quantityAvailable > 0) {
            const overflow = numAdults + numChildren - quantityAvailable;
            const removedChildren = Math.min(numChildren, overflow);
            numChildren -= removedChildren;
            numAdults = Math.max(1, numAdults - (overflow - removedChildren));
            $("#numAdults").val(numAdults);
            $("#numChildren").val(numChildren);
            toastr.error("Mỗi đoàn tối đa " + quantityAvailable + " khách. Vui lòng chia đơn hoặc liên hệ nhân viên.");
        }

        numAdults = parseInt($("#numAdults").val()) || 0;
        numChildren = parseInt($("#numChildren").val()) || 0;

        // Lấy giá từ thuộc tính data-price
        const adultPrice = parseInt($("#numAdults").data("price-adults"));
        const childPrice = parseInt($("#numChildren").data("price-children"));

        // Tính toán tổng giá cho người lớn và trẻ em
        const adultsTotal = numAdults * adultPrice;
        const childrenTotal = numChildren * childPrice;
        $('.coupon-suggestion').each(function () {
            const missing = Math.max(0, Number($(this).data('min-total')) - adultsTotal - childrenTotal);
            $(this).find('.choose-coupon').data('ineligible', missing > 0);
            $(this).find('.coupon-eligibility').text(missing > 0 ? 'Cần thêm ' + missing.toLocaleString('vi-VN') + ' đ để đủ điều kiện.' : 'Đủ điều kiện giá trị đơn.');
        });

        // Cập nhật hiển thị số lượng và giá tiền cho từng loại
        $(".quantity__adults").text(numAdults);
        $(".quantity__children").text(numChildren);
        $(".summary-item:nth-child(1) .total-price").text(
            adultPrice.toLocaleString() + " VNĐ"
        );
        $(".summary-item:nth-child(2) .total-price").text(
            childPrice.toLocaleString() + " VNĐ"
        );

        // Tính tổng giá trị
        const currentCouponCounts = numAdults + ':' + numChildren;
        if (couponCounts !== currentCouponCounts) {
            if (couponRequest) couponRequest.abort();
            discount = 0;
            $('#applied-coupon').val('');
            couponCounts = currentCouponCounts;
        }
        discount = Math.max(0, Math.min(Number(discount) || 0, Math.max(0, adultsTotal + childrenTotal - 1)));
        const transportOption = $('#transport-option option:selected');
        const transportFee = (Number(transportOption.data('fee')) || 0) * (transportOption.data('unit') === 'booking' ? 1 : numAdults + numChildren);
        $('#transport-total').text(transportFee.toLocaleString('vi-VN') + ' VNĐ');
        totalPrice = adultsTotal + childrenTotal - discount + transportFee;
        refreshCouponButtons();
        $(".summary-item:nth-child(3) .total-price").text(discount.toLocaleString() + " VNĐ");
        $(".transfer-amount").text(totalPrice.toLocaleString() + " VNĐ");
        $(".summary-item.total-price span:last").text(
            totalPrice.toLocaleString() + " VNĐ"
        );

        $(".totalPrice").val(totalPrice);
        $(".deposit-price").text((totalPrice * 0.2).toLocaleString() + " VNĐ");
        $('#payment-now-amount').text(Math.round(totalPrice * ($('#payment-plan').val() === 'full' ? 1 : 0.2)).toLocaleString('vi-VN') + ' VNĐ');
        $('#payment-deposit-price').text(Math.round(totalPrice * 0.2).toLocaleString('vi-VN') + ' VNĐ');
        $('#payment-full-price').text(Math.round(totalPrice).toLocaleString('vi-VN') + ' VNĐ');
        renderPassengerFields();
    }

    // Sự kiện tăng/giảm số lượng người lớn và trẻ em
    $(".quantity-selector").on("click", ".quantity-btn", function () {
        if (couponLoading) return;
        if ($('#applied-coupon').val()) {
            toastr.info('Vui lòng hủy mã giảm giá trước khi thay đổi số khách.');
            return;
        }
        const input = $(this).siblings("input");
        const min = parseInt(input.attr("min"));
        let value = parseInt(input.val());
        const quantityAvailable = parseInt(
            $(".quantityAvailable").text().match(/\d+/)[0]
        ); // Lấy số chỗ còn nhận từ nội dung của .quantityAvailable

        // Lấy tổng số lượng người lớn và trẻ em
        const totalAdults = parseInt($("#numAdults").val());
        const totalChildren = parseInt($("#numChildren").val());

        // Kiểm tra nút tăng hay giảm
        if ($(this).text() === "+") {
            // Kiểm tra nếu đang tăng số lượng người lớn
            if (input.attr("id") === "numAdults") {
                // Kiểm tra nếu tổng số người lớn và trẻ em không vượt quá số chỗ còn nhận
                if (totalAdults + totalChildren < quantityAvailable) {
                    value++;
                } else {
                    toastr.error(
                        "Không thể thêm số người lớn vượt quá số chỗ còn nhận!"
                    ); // Thông báo nếu vượt quá
                }
            }
            // Kiểm tra nếu đang tăng số lượng trẻ em
            else if (input.attr("id") === "numChildren") {
                // Kiểm tra nếu tổng số người lớn và trẻ em không vượt quá số chỗ còn nhận
                if (totalAdults + totalChildren < quantityAvailable) {
                    value++;
                } else {
                    toastr.error(
                        "Không thể thêm số trẻ em vượt quá số chỗ còn nhận!"
                    ); // Thông báo nếu vượt quá
                }
            }
        } else if (value > min) {
            value--;
        }

        // Cập nhật số lượng vào input
        input.val(value);

        // Cập nhật lại tổng giá
        updateSummary();
    });

    // Áp dụng mã giảm giá
    $('#payment-plan').on('change', updateSummary);
    $('input[name="payment-plan-choice"]').on('change', function () {
        $('#payment-plan').val($(this).val()).trigger('change');
    });
    $('#transport-option').on('change', function () {
        if (couponLoading || $('#applied-coupon').val()) {
            $(this).val(selectedTransportId);
            toastr.info('Vui lòng hủy mã giảm giá trước khi đổi phương tiện.');
            return;
        }
        selectedTransportId = $(this).val();
        updateSummary();
    });
    $('input[name="transport-choice"]').on('change', function () {
        $('#transport-option').val($(this).val()).trigger('change');
        const selected = String($('#transport-option').val());
        $('input[name="transport-choice"]').each(function () {
            $(this).prop('checked', String($(this).val()) === selected);
        });
    });
    $('.choose-coupon').on('click', function () {
        if (couponLoading || $(this).prop('disabled')) return;
        const code = ($(this).data('code') || '').toString().trim().toUpperCase();
        if ($('#applied-coupon').val() && $('#applied-coupon').val().toUpperCase() === code) {
            $('.btn-coupon').trigger('click');
            return;
        }
        if ($('#applied-coupon').val()) {
            return;
        }
        $('.order-coupon input').val(code).trigger('input');
        $('.btn-coupon').trigger('click');
    });
    $('.order-coupon input').on('input', function () {
        if (couponLoading || $('#applied-coupon').val()) return;
        if (couponRequest) couponRequest.abort();
        discount = 0;
        $('#applied-coupon').val('');
        updateSummary();
    });
    $(".btn-coupon").on("click", function (e) {
        e.preventDefault();

        if (couponLoading) return;

        if ($('#applied-coupon').val()) {
            const clearUrl = $('.order-coupon').data('clear-url');
            if (clearUrl) {
                couponLoading = true;
                refreshCouponButtons();
                couponRequest = $.ajax({
                    url: clearUrl, method: 'POST',
                    headers: { Accept: 'application/json' },
                    data: { _token: $("form.booking-container input[name='_token']").val(), code: $('#applied-coupon').val() },
                    success: function () {
                        couponRequest = null;
                        clearCouponSelection({ resetInput: true, silent: true });
                    },
                    error: function () { toastr.error('Chưa hủy được mã. Vui lòng thử lại.'); },
                    complete: function () { couponRequest = null; couponLoading = false; refreshCouponButtons(); }
                });
            } else {
                clearCouponSelection({ resetInput: true, silent: true });
            }
            return;
        }

        const couponCode = $(".order-coupon input").val().trim().toUpperCase();

        if (couponRequest) couponRequest.abort();
        discount = 0;
        $('#applied-coupon').val('');
        updateSummary();
        if (!couponCode) return;
        const counts = couponCounts;
        couponLoading = true;
        refreshCouponButtons();
        couponRequest = $.ajax({
            url: $('.order-coupon').data('url'), method: 'POST',
            headers: { Accept: 'application/json' },
            data: { _token: $("form.booking-container input[name='_token']").val(),
                tourId: $('#tourId').val(), adults: $('#numAdults').val(), children: $('#numChildren').val(), code: couponCode,
                transportOptionId: $('#transport-option').val() || 0 },
            success: function (response) {
                if (counts !== couponCounts) return;
                discount = response.discount;
                $('#applied-coupon').val(response.code);
                refreshCouponButtons();
                updateSummary();
                toastr.success('Đã áp dụng mã ' + response.code);
            },
            complete: function () { couponRequest = null; couponLoading = false; refreshCouponButtons(); },
            error: function (xhr, status) {
                if (status === 'abort') return;
                const errors = xhr.responseJSON && xhr.responseJSON.errors;
                toastr.error(errors ? Object.values(errors).flat()[0] : 'Không kiểm tra được mã. Vui lòng thử lại.');
            }
        });
    });

    // Sự kiện khi thay đổi trạng thái checkbox
    $("#agree").on("change", function () {
        toggleButtonState();
    });

    // Hàm thay đổi trạng thái của nút
    function toggleButtonState() {
        if ($("#agree").is(":checked")) {
            $(".btn-submit-booking")
                .removeClass("inactive")
                .css("pointer-events", "auto");
        } else {
            $(".btn-submit-booking")
                .addClass("inactive")
                .css("pointer-events", "none");
        }
    }

    function validateBookingForm() {
        let isValid = true;
        if (couponLoading) { toastr.error('Đang kiểm tra mã giảm giá. Vui lòng chờ.'); return false; }
        const form = document.querySelector('form.booking-container');
        if (!form || !form.reportValidity()) return false;
        if (!$("#agree").is(":checked")) {
            toastr.error("Vui lòng đồng ý với điều khoản thanh toán.");
            return false;
        }
        const requestedSeats = Number($("#numAdults").val()) + Number($("#numChildren").val());
        if (!Number.isInteger(requestedSeats) || requestedSeats < 1 || requestedSeats > getQuantityAvailable()) {
            toastr.error("Số khách vượt quá số chỗ còn lại. Vui lòng kiểm tra lại.");
            return false;
        }
        const passengerCount = parseInt($("#numAdults").val()) + parseInt($("#numChildren").val());
        const passengerFile = $("#passenger-file").val();
        const usingPassengerFile = passengerCount > 2 && passengerFile !== "";
        if (passengerCount > 2 && !passengerFileReady) {
            toastr.error("Vui lòng chọn file và chờ kiểm tra thành công trước khi đặt tour.");
            return false;
        }


        // Xóa thông báo lỗi cũ
        $(".error-message").hide();

        // Kiểm tra họ và tên (không được để trống)
        const username = $("#username").val().trim();
        if (!usingPassengerFile && username === "") {
            $("#usernameError").text("Họ và tên không được để trống").show();
            isValid = false;
        }

        // Kiểm tra email (phải đúng định dạng email)
        const email = $("#email").val().trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!usingPassengerFile && email === "") {
            $("#emailError").text("Email không được để trống").show();
            isValid = false;
        } else if (!usingPassengerFile && !emailPattern.test(email)) {
            $("#emailError").text("Email không đúng định dạng").show();
            isValid = false;
        }

        // Kiểm tra số điện thoại (phải là số và từ 10-11 ký tự)
        const tel = $("#tel").val().trim();
        const telPattern = /^[0-9]{10,11}$/;
        if (!usingPassengerFile && tel === "") {
            $("#telError").text("Số điện thoại không được để trống").show();
            isValid = false;
        } else if (!usingPassengerFile && !telPattern.test(tel)) {
            $("#telError").text("Số điện thoại phải có 10-11 chữ số").show();
            isValid = false;
        }

        // Kiểm tra địa chỉ (không được để trống)
        const address = $("#address").val().trim();
        if (!usingPassengerFile && address === "") {
            $("#addressError").text("Địa chỉ không được để trống").show();
            isValid = false;
        }

        const paymentMethod = $("input[name='payment']:checked").val();
        if (!paymentMethod) {
            toastr.error("Vui lòng chọn phương thức thanh toán.");
            isValid = false;
        }
        return isValid; // Trả về kết quả kiểm tra
    }
    // Kiểm tra tính hợp lệ khi nhấn nút submit
    $("form.booking-container").has("#numAdults").on("submit", function (e) {
        e.preventDefault();

        // Nếu tất cả đều hợp lệ, gửi form
        if (validateBookingForm()) {
            $(this).find('.btn-submit-booking').prop('disabled', true).text('Đang gửi đặt tour…');
            HTMLFormElement.prototype.submit.call(this);
        }
    });

    // Hàm kiểm tra giá trị lựa chọn thanh toán
    $('input[name="payment"]').change(function () {
        const paymentMethod = $(this).val();
        $("#payment_hidden").val(paymentMethod);
        const isPaymentSelected =
            paymentMethod === "paypal-payment" ||
            paymentMethod === "momo-payment";

        const isBankTransfer = paymentMethod === "bank-transfer";
        $("#bank-transfer-info").toggle(isBankTransfer);
        if (isBankTransfer && totalPrice > 0) {
            $(".transfer-amount").text(totalPrice.toLocaleString() + " VNĐ");
        }

        $(".btn-submit-booking").toggle(!isPaymentSelected); // Ẩn hoặc hiện nút xác nhận

        if (paymentMethod === "paypal-payment") {
            if (typeof paypal === 'undefined' || typeof paypal.Buttons !== 'function') {
                toastr.error('PayPal chưa được cấu hình. Vui lòng kiểm tra PAYPAL_SANDBOX_CLIENT_ID trong file .env.');
                $("#paypal-button-container").hide();
                return;
            }

            var totalPricePayment = totalPrice / 25000; //switch to USD
            paypal
                .Buttons({
                    createOrder: function (data, actions) {
                        return actions.order.create({
                            purchase_units: [
                                {
                                    amount: {
                                        value: totalPricePayment.toFixed(2), // Số tiền thanh toán
                                    },
                                },
                            ],
                        });
                    },
                    onApprove: function (data, actions) {
                        return actions.order.capture().then(function (details) {
                            // Hiển thị thông tin thanh toán thành công
                            console.log(
                                "Transaction completed by " +
                                    details.payer.name.given_name
                            );
                            // Tạo input hidden mới
                            var hiddenInput = $("<input>", {
                                type: "hidden", // Loại input là hidden
                                name: "transactionIdPaypal", // Tên của input
                                value: details.id, // Giá trị là transactionId
                            });

                            // Thêm input hidden vào form
                            $('input[name="payment"]:checked')
                                .closest("form")
                                .append(hiddenInput);
                            toastr.success("Thanh toán thành công!");
                            $("#paypal-button-container").hide(); // Ẩn nút PayPal

                            // Vô hiệu hóa tất cả các radio button
                            $('input[name="payment"]').prop("disabled", true);

                            $(".btn-submit-booking").show(); // Hiện nút xác nhận
                        });
                    },
                    onError: function (err) {
                        console.error(err);
                        toastr.error(
                            "Có lỗi xảy ra trong quá trình thanh toán."
                        );
                    },
                })
                .render("#paypal-button-container"); // Render nút PayPal vào thẻ chứa
        } else {
            // Nếu không phải là PayPal, ẩn nút chứa button PayPal
            $("#paypal-button-container").empty(); // Xóa nút PayPal nếu có
        }
        if (paymentMethod === "momo-payment") {
            $("#btn-momo-payment").show();
        } else {
            $("#btn-momo-payment").hide();
        }
    });

    // Save form data to localStorage before payment
    $("#btn-momo-payment").click(function (e) {
        e.preventDefault();
        var urlMomo = $(this).data("urlmomo");

        if (validateBookingForm()) {
            // Gather form data
            var bookingData = {
                fullName: $("#username").val(),
                email: $("#email").val(),
                tel: $("#tel").val(),
                address: $("#address").val(),
                numAdults: $("#numAdults").val(),
                numChildren: $("#numChildren").val(),
                payment: $("input[name='payment']:checked").val(),
                payment_hidden: $("#payment_hidden").val(),
            };
            console.log(bookingData);

            // Save to localStorage
            localStorage.setItem("bookingData", JSON.stringify(bookingData));

            const passengers = $('.passenger-card').slice(1).map(function () {
                return {
                    fullName: $(this).find('input[name^="passengers"][name$="[fullName]"]').val(),
                    email: $(this).find('input[name^="passengers"][name$="[email]"]').val(),
                    tel: $(this).find('input[name^="passengers"][name$="[tel]"]').val(),
                    address: $(this).find('input[name^="passengers"][name$="[address]"]').val(),
                };
            }).get();
            const importedPassengers = $("#passenger-import-json").val();

            $.ajax({
                url: urlMomo,
                method: "POST",
                data: {
                    amount: totalPrice,
                    tourId: $("input[name='tourId']").val(),
                    bookingData: JSON.stringify({
                        fullName: $("#username").val(),
                        email: $("#email").val(),
                        tel: $("#tel").val(),
                        address: $("#address").val(),
                        numAdults: $("#numAdults").val(),
                        numChildren: $("#numChildren").val(),
                        totalPrice: totalPrice,
                        tourId: $("input[name='tourId']").val(),
                        passengers: importedPassengers ? JSON.parse(importedPassengers).slice(1) : passengers,
                    }),
                    _token: $('input[name="_token"]').val(),
                },
                success: function (response) {
                    if (response && response.payUrl) {
                        window.location.href = response.payUrl;
                    } else {
                        toastr.error("Không thể tạo thanh toán Momo.");
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "Có lỗi xảy ra khi kết nối đến Momo.";
                    toastr.error(msg);
                },
            });
        }
    });

    var savedData = localStorage.getItem("bookingData");
    if (savedData && $("#numAdults").length) {
        var bookingData = JSON.parse(savedData);
        console.log(bookingData);

        $("#username").val(bookingData.fullName);
        $("#email").val(bookingData.email);
        $("#tel").val(bookingData.tel);
        $("#address").val(bookingData.address);
        $("#numAdults").val(bookingData.numAdults);
        $("#numChildren").val(bookingData.numChildren);
        $("input[name='payment'][value='" + bookingData.payment + "']").prop(
            "checked",
            true
        );
        $("#payment_hidden").val(bookingData.payment_hidden);
        $("#agree").prop("checked", true);
        // Vô hiệu hóa tất cả các radio button
        $('input[name="payment"]').prop("disabled", true);

        // Clear booking data after populating the form
        localStorage.removeItem("bookingData");
    }
    // Khởi tạo tổng giá khi trang vừa tải
    updateSummary();
    toggleButtonState();

    /****************************************
     *             PAGE BOOKED              *
     * ***************************************/

    /****************************************
     *             PAGE TOURDETAIL          *
     * ***************************************/

    let currentRating = 0;

    $("#rating-stars i").on("mouseover", function () {
        let rating = $(this).data("value");
        highlightStars(rating);
    });

    $("#rating-stars i").on("click", function () {
        currentRating = $(this).data("value");
        console.log("Sao đã chọn :", currentRating);
    });

    $("#rating-stars i").on("mouseout", function () {
        resetStars();
        if (currentRating > 0) {
            highlightStars(currentRating);
        }
    });

    // Hàm tô màu các sao được chọn
    function highlightStars(rating) {
        $("#rating-stars i").each(function () {
            if ($(this).data("value") <= rating) {
                $(this).removeClass("far").addClass("fas active");
            } else {
                $(this).removeClass("fas active").addClass("far");
            }
        });
    }

    // Hàm đặt lại tất cả sao về trạng thái chưa chọn
    function resetStars() {
        $("#rating-stars i").each(function () {
            $(this).removeClass("fas active").addClass("far");
        });
    }
    let urlCheckBooking = $("#submit-reviews").attr("data-url-checkBooking");
    let urlSubmitReview = $("#comment-form").attr("action");
    let tourIdReview = $("#submit-reviews").attr("data-tourId-reviews");

    $("#comment-form").on("submit", function (e) {
        e.preventDefault();

        let message = $("#message").val().trim();

        // Kiểm tra số sao và nội dung
        if (currentRating === 0) {
            toastr.warning("Vui lòng chọn số sao để đánh giá.");
            return;
        } else if (message === "") {
            toastr.warning("Vui lòng nhập nội dung phản hồi.");
            return;
        }

        $.ajax({
            url: urlCheckBooking,
            method: "POST",
            data: {
                tourId: tourIdReview,
                _token: $('input[name="_token"]').val(),
            },
            success: function (response) {
                if (response.success) {
                    formReviews = {
                        tourId: tourIdReview,
                        rating: currentRating,
                        message: message,
                        _token: $('input[name="_token"]').val(),
                    };

                    // Gửi AJAX request
                    $.ajax({
                        url: urlSubmitReview, // Lấy URL từ action của form
                        method: "POST",
                        data: formReviews,
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $("#partials_reviews").html(response.data);
                                $("#partials_reviews .comment-body").addClass(
                                    "aos-animate"
                                );
                                // Xử lý reset form hoặc thông báo
                                $("#message").val("");
                                $('#comment-form').hide();
                                resetStars();
                                currentRating = 0;
                            }
                        },
                        error: function (xhr, status, error) {
                            toastr.error("Đã có lỗi xảy ra. Vui lòng thử lại.");
                            console.error("Error:", error);
                        },
                    });
                } else {
                    toastr.error(
                        "Vui lòng đặt tour và trải nghiệm để có thể đánh giá!"
                    );
                }
            },
            error: function (xhr, status, error) {
                toastr.error("Đã có lỗi xảy ra. Vui lòng thử lại.");
                console.error("Error:", error);
            },
        });
    });

    /****************************************
     *             PAGE CONTACT             *
     * ***************************************/


    $('#contactForm').submit(function (event) {
        event.preventDefault();

        var name = $('#name').val();
        var phoneNumber = $('#phone_number').val();
        var message = $('#message').val();

        $('.error').remove();

        if (sqlInjectionPattern.test(name)) {
            $('#name').after('<span class="error" style="color: red;">Vui lòng nhập tên hợp lệ và không chứa ký tự đặc biệt.</span>');
            return false;
        }

        if (sqlInjectionPattern.test(phoneNumber)) {
            $('#phone_number').after('<span class="error" style="color: red;">Vui lòng nhập số điện thoại hợp lệ và không chứa ký tự đặc biệt.</span>');
            return false;
        }


        this.submit();
    });
    /****************************************
     *             HANDLE SEARCH            *
     * ***************************************/

    $('#search_form').on('submit', function(event) {
        // Lấy giá trị các trường cần kiểm tra
        var destination = $('#destination').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (destination === "") {
            event.preventDefault();
            toastr.error('Vui lòng chọn điểm đến.');
            return;
        }

        // Chuyển đổi định dạng ngày từ DD/MM/YYYY sang YYYY-MM-DD
        function convertDateFormat(date) {
            var parts = date.split('/');
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }

        if (startDate && endDate) {
            var startDateFormatted = new Date(convertDateFormat(startDate));
            var endDateFormatted = new Date(convertDateFormat(endDate));

            // Kiểm tra nếu "start_date" lớn hơn "end_date"
            if (startDateFormatted > endDateFormatted) {
                event.preventDefault();
                toastr.error('Ngày khởi hành không thể lớn hơn ngày kết thúc.');
                return;
            }
        }
    });


    /****************************************
     *  HANDLE SEARCH Speech Recognition    *
     * ***************************************/

     // Kiểm tra nếu trình duyệt hỗ trợ Speech Recognition
     if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        var recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'vi-VN'; // Cài đặt ngôn ngữ nhận diện
        recognition.continuous = true; // Tiếp tục nhận diện khi đang nói
        recognition.interimResults = true; // Hiển thị kết quả tạm thời khi nhận diện
    
        // Biến để theo dõi trạng thái nhận diện
        var isRecognizing = false;
    
        $('#voice-search').on('click', function() {
            if (isRecognizing) {
                recognition.stop(); // Dừng nhận diện nếu đang nhận diện
                $(this).removeClass('fa-microphone-slash').addClass('fa-microphone'); // Đổi icon về micro
            } else {
                recognition.start(); // Bắt đầu nhận diện giọng nói
                $(this).removeClass('fa-microphone').addClass('fa-microphone-slash'); // Đổi icon thành micro gạch
            }
        });
    
        recognition.onstart = function() {
            console.log('Speech recognition started');
            isRecognizing = true; // Đánh dấu trạng thái nhận diện
            $('#voice-search').removeClass('fa-microphone').addClass('fa-microphone-slash'); // Đổi icon thành micro gạch
        };
    
        recognition.onresult = function(event) {
            var transcript = event.results[0][0].transcript; // Lấy kết quả nhận diện
            if (event.results[0].isFinal) {
                // Kết quả cuối cùng, điền vào ô tìm kiếm
                $('input[name="keyword"]').val(transcript);
            } else {
                // Kết quả tạm thời, có thể cập nhật ô tìm kiếm
                $('input[name="keyword"]').val(transcript);
            }
        };
    
        recognition.onerror = function(event) {
            console.log('Speech recognition error', event.error);
            toastr.error('Có lỗi xảy ra khi nhận diện giọng nói: ' + event.error);
        };
    
        recognition.onend = function() {
            console.log('Speech recognition ended');
            $('#voice-search').removeClass('fa-microphone-slash').addClass('fa-microphone'); // Đổi icon về micro
            isRecognizing = false; // Đánh dấu trạng thái nhận diện kết thúc
        };
    } else {
        console.log('Speech recognition not supported in this browser.');
        toastr.error('Trình duyệt của bạn không hỗ trợ nhận diện giọng nói.');
    }
    
    
});
