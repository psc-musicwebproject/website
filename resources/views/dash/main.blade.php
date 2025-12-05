<x-dash.layout>
    <div class="alert alert-warning">
        <h4><i class="bi bi-exclamation-triangle"></i> ฟีเจอร์นี้อยู่ในระหว่างการพัฒนา</h4>
        <p class="mb-0">ขออภัยในความไม่สะดวก ฟีเจอร์แดชบอร์ดยังไม่พร้อมใช้งานในขณะนี้ กรุณาติดต่อผู้ดูแลระบบสำหรับข้อมูลเพิ่มเติม</p>
    </div>
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.AllBooking />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.ApproveBooking />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.WaitingBooking />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.RejectedBooking />
        </div>
    </div>
</x-dash.layout>
