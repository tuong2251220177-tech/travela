@include('clients.blocks.chan-trang-giao-dien')
@include('clients.blocks.tro-ly-ai')
<script src="{{ asset('clients/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>window.travelaAiChatUrl = @json(route('ai-chat')); window.travelaCsrfToken = @json(csrf_token());</script>
@if($bookingScripts ?? false)
    <script src="{{ asset('clients/assets/js/jquery.datetimepicker.full.min.js') }}"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
    <script src="{{ asset('clients/assets/js/custom-js.js?v=20260930-payment-cards') }}"></script>
    <script src="{{ asset('clients/assets/js/bank-qr.js') }}"></script>
@endif
<script src="{{ asset('clients/assets/js/ai-chat.js?v=20260916-qwen') }}"></script>
<script src="{{ asset('clients/assets/js/tour-stitch.js') }}"></script>
</body>
</html>
