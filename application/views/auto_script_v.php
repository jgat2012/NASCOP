<script type="text/javascript">
	 $(document).ready(function(){
	 	var base_url="<?php echo base_url();?>";
         //run eid_sync to get eid data
         var link=base_url+"settings/eid_sync";
         $.ajax({
         	url:link,
         	success:function(){

         	}
         })
	 });
</script>


<div class="container-fluid">
  <div class="row-fluid">
      <div class="span12">
         <div class="table-responsive">
            <table id="task_data" class="table table-bordered table-hover table-condensed">
              <thead>
              <tr>
               <th>Task</th>
               <th>Status</th>
               </tr>
              </thead>
              <tbody>
               <tr>
               <td>Task</td>
               <td>Status</td>
               </tr>
              </tbody>
            </table>
         </div>
      </div>
  </div>
</div>