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

								<li class="breadcrumb-item active" aria-current="page">Payment Data</li>

							</ol>

						</nav>

					</div>

					

				</div>

				<!--end breadcrumb-->

			  

				<div class="card">

					<div class="card-body">

						<div class="d-lg-flex align-items-center justify-content-between mb-4 gap-3">

    <!-- 🔍 Left: Search -->
    <div class="position-relative">
        <input
            type="text"
            id="serchPlot"
            class="form-control ps-5 radius-30"
            placeholder="Search Buyer"
        >
        <span class="position-absolute top-50 product-show translate-middle-y">
            <i class="bx bx-search"></i>
        </span>

        <input type="hidden" id="buyer_id" name="buyer_id"
               value="<?= isset($buyer_id) ? $buyer_id : '' ?>">
    </div>

    <!-- 🖨 Right: Print Button -->
    <div class="ms-lg-auto">
        <button type="button" class="btn btn-primary radius-30" id="printBtn">
            <i class="bx bx-printer me-1"></i> Print
        </button>
    </div>

</div>

						<div class="table-responsive">

							<table class="table mb-0" id="">

								<thead class="table-light">
  <tr>
    <th>Index</th>
    <th>User Name</th>
    <th>Buyer Name</th>
    <th>Site Name</th>
    <th>Plot Number</th>
    <th>paid At</th>
    <th>paid Amount</th>
     <th>Actions</th>
  </tr>
</thead>


								<tbody id="payment_data">

									

								</tbody>

							</table>

						</div>

					</div>

                    <nav aria-label="Page navigation example">

							<ul class="pagination round-pagination justify-content-center">

								<li class="page-item"><a class="page-link" href="javascript:;">Previous</a>

								</li>

								<li class="page-item"><a class="page-link" href="javascript:;javascript:;">1</a>

								</li>

								<li class="page-item active"><a class="page-link" href="javascript:;">2</a>

								</li>

								<li class="page-item"><a class="page-link" href="javascript:;">3</a>

								</li>

								<li class="page-item"><a class="page-link" href="javascript:;">Next</a>

								</li>

							</ul>

</nav>

				</div>





			</div>

</div>
