<div class="tile" id="drugs-chart">
	<h3>Top
	<select style="width:auto" class="period">
		<option value="5" selected=selected>5</option>
		<option value="10">10</option>
		<option value="15">15</option>
		<option value="20">20</option>
		<option value="25">25</option>
	</select> Commodities Ordered in
	<input type="text"  class="input-medium" id="reporting_period_1"/>
	<input type="hidden"  class="input-medium" id="period_start_date_1"/>
	<input type="hidden"  class="input-medium" id="period_end_date_1"/>
	<button class="generate btn" id="expiry_btn">
		Get
	</button>
	<br/>
	<button class="btn btn-success more" id="drugs-more">
		Larger
	</button>
	<button class="btn btn-danger less" id="drugs-less">
		Smaller
	</button></h3>

	<div id="chart_area">
		<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo base_url().'Images/loading_spin.gif' ?>">
		</div>
	</div>

</div>

<div class="tile" id="enrollment-chart">
	<h3>Facilities Ordering using webADT in
	<input type="text"  class="input-medium" id="reporting_period_2"/>
	<input type="hidden"  class="input-medium" id="period_start_date_2"/>
	<input type="hidden"  class="input-medium" id="period_end_date_2"/>
	<button class="btn generate" id="enrollment_btn">
		Get
	</button> </br/>
	<button class="btn btn-success more" id="enrollment-more">
		Larger
	</button>
	<button class="btn btn-danger less" id="enrollment-less">
		Smaller
	</button></h3>
	<div id="chart_area2">
		<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo base_url().'Images/loading_spin.gif' ?>">
		</div>
	</div>
</div>
<div class="tile" id="appointments-chart">
	<h3>Pipeline Picking Lists for
	<input type="text"  class="input-medium" id="reporting_period_3"/>
	<input type="hidden"  class="input-medium" id="period_start_date_3"/>
	<input type="hidden"  class="input-medium" id="period_end_date_3"/>
	<button class="generate btn" id="appointment_btn">
		Get
	</button>
	<br/>
	<button class="btn btn-success more" id="appointment-more">
		Larger
	</button>
	<button class="btn btn-danger less" id="appointment-less">
		Smaller
	</button></h3>
	<div id="chart_area3">
		<div class="loadingDiv" style="margin:20% 0 20% 0;"><img style="width: 30px;margin-left:50%" src="<?php echo base_url().'Images/loading_spin.gif' ?>">
		</div>
	</div>
</div>
<div class="tile" id="stocks-chart">
	<h3>Facilities Delaying Orders in
	<input type="text"  class="input-medium" id="reporting_period_4"/>
	<input type="hidden"  class="input-medium" id="period_start_date_4"/>
	<input type="hidden"  class="input-medium" id="period_end_date_4"/>
	<input type="hidden"  class="input-medium"/>
	<button class="generate btn" id="stockout_btn">
		Get
	</button>
	<br/>
	<button class="btn btn-success more" id="stock-more">
		Larger
	</button>
	<button class="btn btn-danger less" id="stock-less">
		Smaller
	</button></h3>

	<div id="chart_area4">
		<div class="loadingDiv" style="margin:20% 0 20% 0;" ><img style="width: 30px;margin-left:50%" src="<?php echo base_url().'Images/loading_spin.gif' ?>">
		</div>
	</div>
</div>