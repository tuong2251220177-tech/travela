</main>
@include('clients.blocks.chan-trang-giao-dien')
@include('clients.blocks.tro-ly-ai')
</div>
@if(session('error'))<div class="site-flash" role="alert">{{ session('error') }}</div>@endif
    <!-- Jquery -->
    <script src="{{asset('clients/assets/js/jquery-3.6.0.min.js')}}"></script>
    <!-- Bootstrap -->
    <script src="{{asset('clients/assets/js/bootstrap.min.js')}}"></script>
    <!-- Appear Js -->
    <script src="{{asset('clients/assets/js/appear.min.js')}}"></script>
    <!-- Slick -->
    <script src="{{asset('clients/assets/js/slick.min.js')}}"></script>
    <!-- Magnific Popup -->
    <script src="{{asset('clients/assets/js/jquery.magnific-popup.min.js')}}"></script>
    <!-- Nice Select -->
    <script src="{{asset('clients/assets/js/jquery.nice-select.min.js')}}"></script>
    <!-- Image Loader -->
    <script src="{{asset('clients/assets/js/imagesloaded.pkgd.min.js')}}"></script>
    <!-- Skillbar -->
    <script src="{{asset('clients/assets/js/skill.bars.jquery.min.js')}}"></script>
    <!-- Jquery UI -->
    <script src="{{asset('clients/assets/js/jquery-ui.min.js')}}"></script>
    <!-- Isotope -->
    <script src="{{asset('clients/assets/js/isotope.pkgd.min.js')}}"></script>
    <!--  AOS Animation -->
    <script src="{{asset('clients/assets/js/aos.js')}}"></script>
    <!-- Custom script -->
    <script src="{{asset('clients/assets/js/script.js')}}"></script>
    {{-- jquery-toast  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- paypal-payment  --}}

    <!-- Custom script by Dev dien-->
    <script src="{{ asset('clients/assets/js/custom-js.js?v=20260929-transport') }}"></script>
    <script>
        window.travelaAiChatUrl = "{{ route('ai-chat') }}";
        window.travelaCsrfToken = "{{ csrf_token() }}";
    </script>
    <script src="{{asset('clients/assets/js/ai-chat.js?v=20260916-qwen')}}"></script>
    <script src="{{asset('clients/assets/js/jquery.datetimepicker.full.min.js')}}"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

<script src="{{ asset('clients/assets/js/tour-stitch.js') }}"></script>
</body>

</html>
