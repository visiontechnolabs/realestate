<div class="page-wrapper">

			<div class="page-content">

				<!--breadcrumb-->

				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">

					<!-- <div class="breadcrumb-title pe-3">Category</div> -->

					<div class="ps-3">

						<nav aria-label="breadcrumb">

							<ol class="breadcrumb mb-0 p-0">

								<li class="breadcrumb-item"><a href="<?= base_url('dashboard');?>"><i class="bx bx-home-alt"></i></a>

								</li>

								<li class="breadcrumb-item active" aria-current="page">Buyer Details</li>

							</ol>

						</nav>

					</div>

					

				</div>

				<!--end breadcrumb-->
                <div class="card">

					<div class="card-body">

    <h5 class="card-title fw-bold my-4">Customer Information</h5>

    <div class="d-flex flex-column w-100">

        <!-- Name -->
        <div class="row my-2">
            <div class="col fw-bold">Full Name</div>
            <div class="col text-start">
                <?= isset($buyer->name) ? $buyer->name : "-" ?>
            </div>
        </div>
        <hr>

        <!-- Mobile -->
        <div class="row my-2">
            <div class="col fw-bold">Mobile Number</div>
            <div class="col text-start">
                <?= isset($buyer->mobile) ? $buyer->mobile : "-" ?>
            </div>
        </div>
        <hr>

        <!-- Email -->
        <div class="row my-2">
            <div class="col fw-bold">Email</div>
            <div class="col text-start">
                <?= isset($buyer->email) ? $buyer->email : "-" ?>
            </div>
        </div>
        <hr>

        <!-- Address -->
        <div class="row my-2">
            <div class="col fw-bold">Address</div>
            <div class="col text-start">
                <?= isset($buyer->address) ? $buyer->address : "-" ?>
            </div>
        </div>
        <hr>

        <h5 class="fw-bold my-4">Plot Information</h5>

        <div class="row my-2">
            <div class="col fw-bold">Plot Number</div>
            <div class="col text-start">
                <?= isset($plot->plot_number) ? $plot->plot_number : "-" ?>
            </div>
        </div>
        <hr>

        <div class="row my-2">
            <div class="col fw-bold">Site</div>
            <div class="col text-start">
                <?= isset($plot->site_name) ? $plot->site_name : "-" ?>

            </div>
        </div>
        <hr>

        <h5 class="fw-bold my-4">Payment Information</h5>

        <div class="row my-2">
            <div class="col fw-bold">Total Price</div>
            <div class="col text-start">
                ₹<?= isset($payment->total_price) ? number_format($payment->total_price) : "-" ?>
            </div>
        </div>
        <hr>

        <div class="row my-2">
            <div class="col fw-bold">Payment Mode</div>
            <div class="col text-start">
                <?= isset($payment->payment_mode) ? ucfirst($payment->payment_mode) : "-" ?>
            </div>
        </div>
        <hr>

        <div class="row my-2">
    <div class="col fw-bold">Remaining Payment</div>
    <div class="col text-start">
        ₹<?= isset($remaining_amount) ? number_format($remaining_amount) : "-" ?>
    </div>
</div>

        <hr>

        <?php if(isset($payment->payment_mode) && $payment->payment_mode == "EMI"): ?>

            <h5 class="fw-bold my-4">EMI Information</h5>

            <div class="row my-2">
                <div class="col fw-bold">Monthly EMI</div>
                <div class="col text-start">
                    ₹<?= isset($emi[0]->emi_amount) ? $emi[0]->emi_amount : "-" ?>
                </div>
            </div>
            <hr>

            <div class="row my-2">
                <div class="col fw-bold">Duration (Months)</div>
                <div class="col text-start">
                    <?= isset($emi[0]->month_no) ? count($emi) : "-" ?>
                </div>
            </div>
            <hr>

            <div class="row my-2">
                <div class="col fw-bold">EMI Start Date</div>
                <div class="col text-start">
                    <?= isset($emi[0]->emi_date) ? $emi[0]->emi_date : "-" ?>
                </div>
            </div>
            <hr>

        <?php endif; ?>

        <!-- WhatsApp + Call -->
        <div class="mt-4 w-100 d-flex justify-content-between">
            <a href="https://wa.me/91<?= $buyer->mobile ?>" target="_blank"
               class="btn btn-success w-50 me-2">
                <i class="lni lni-whatsapp"></i> WhatsApp
            </a>

           <a href="<?= base_url('payment_data/' . $buyer->id) ?>" 
   class="btn btn-primary w-50 ms-2">
    <i class="lni lni-wallet"></i> Check Payment Data
</a>

        </div>

    </div>
</div>

</div>
			  

</div>
</div>