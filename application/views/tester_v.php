<?php echo $this->session->userdata('assets'); ?>
<!--Style-->
<style type="text/css">
	.main_holder {
		width: 100%;
		height: 100%;
		background: #FFF;
	}
	body {
		font-family: "Helvetica";
		line-height: 1;
		font-size: 1em;
		vertical-align: baseline;
		margin: 0px;
	}
	#footer_text {
		margin: 0 auto;
		font-size:0.8em;
		color: #000;
		width: 90%;
		height: 30px;
		display: block;
		overflow: hidden;
		letter-spacing:0px;
	}
	#footer {
		width: 95%;
		margin: 5px 2% 5px 2%;
		text-align: center;
	}
	#bottom_ribbon {
		background: white;
		height: 30px;
		width: 100%;
		position: fixed;
		bottom: 0;
		margin: 0 auto;
		border-top: 2px solid #DDD;
	}
	#top_ribbon {
		background:#2B597E;
		height: 25px;
		width: 100%;
		position: relative;
		bottom: 0;
		margin: 0 auto;
		color: #FFF;
		border-top: 2px solid #DDD;
	}
	#header {
		width: 95%;
		margin: 5px 2% 5px 2%;
	}
	#whole_content {
		border: 2px solid #DDDFFF;
		position: relative;
		width: auto;
		height: 94%;
		margin-top: 10px;
	}
	.content_message {
		color: #FFF;
		background:#2B597E;
		width: auto;
		height: 1em;
		padding: 0.3em;
		font-size: 0.9em;
	}
	.content_body {
		background: #FFF;
		width: auto;
		height:98%;
		margin: 0 auto;
	}
	.nav-tabs>li{
		background:#DDDFFF;
		border:1px solid #DDD;
		color:#FFF;
	}
	.nav>li>a{
		color:#000;
		font-size:0.8em;
	}

	.content_upper{
		height:45%;
		margin:0 auto;
		padding:0.1em;
		width:99%;
	}
	
	.content_lower{
		height:45%;
		margin:0 auto;
		padding:0.1em;
		width:99%;
	}
	.column_left{
		padding:0.5%;
		display:inline-block;
		height:95%;
		width:32.5%;
		border:1px solid #DDD;
	}
	.column_middle{
		padding:0.5%;
		height:95%;
		width:31.5%;
		border:1px solid #DDD;
		display:inline-block;
	}
	.column_right{
		padding:0.5%;
		display:inline-block;
		height:95%;
		width:31.5%;
		border:1px solid #DDD;
	}
	
	.nav-tabs{
		margin-bottom:0px;
	}
	
	.content_dashboard{
		width:100%;
		height:auto;
		margin:0 auto;
	}
	.content_label{
		width:inherit;
		height:20px;
		margin:0 auto;
		background:#999;
		color:#FFF;
		position:absolute;
		text-align:center;
		padding-top:0.4em;
	}
	
	
	
	
</style>
<!--Jquery-->
<script type="text/javascript">
	$(document).ready(function() {

	});

</script>
<!--Html-->
<div class="main_holder">
	<!--Header-->
	<div id="top_ribbon">
		<div id="header"></div>
	</div>
	<div id="whole_content">
		<div class="content_message">
			National Dashboard
		</div>
		<div class="content_body">
			<div class="content_menu">
					<ul class="nav nav-tabs " >
						<li id="ca_menu" class="active main_menu">
							<a href="#tab1" data-toggle="tab">Commodity Analysis</a>
						</li>
						<li id="pa_menu" class="main_menu">
							<a href="#tab2" data-toggle="tab">Patient Analysis</a>
						</li>
						<li id="fa_menu" class="main_menu">
							<a href="#tab3" data-toggle="tab">Facility Analysis</a>
						</li>
						<li id="oa_menu" class="order_analysis_menus main_menu">
							<a href="#tab4" data-toggle="tab">Order Analysis</a>
						</li>
						<li id="ra_menu" class="main_menu">
							<a href="#tab5" data-toggle="tab">Reporting Analysis</a>
						</li>
					</ul>
				</div>
				<div class="content_dashboard">
					<div class="content_upper">
						<div class="column_left">
							<div class="content_label">
								Commodity Stock Levels at Pipeline
							</div>
						</div>
						<div class="column_middle">
							<div class="content_label">
								Commodity Stock Levels at Facility
							</div>
						</div>
						<div class="column_right" >
							<div class="content_label">
								Commodity Stock Outs 
							</div>
						</div>
					</div>
					<div class="content_lower">
						<div class="column_left">
							<div class="content_label">
								Summary Stock Levels 
							</div>
						</div>
						<div class="column_middle">
							<div class="content_label">
								Graphs Analysis
							</div>
						</div>
						<div class="column_right" >
							<div class="content_label">
								Combined Analysis
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--Footer-->
	<div id="bottom_ribbon">
		<div id="footer">
			<?php $this -> load -> view('footer_v');?>
		</div>
	</div>
</div>
