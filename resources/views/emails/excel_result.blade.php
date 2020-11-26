<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<p>Hi,</p> <br/>
	@if($status == 1)
		<b>CSV Execution Completed</b>
		@if(!empty($processed))
			<p>Number of rows processed: {{$processed}}</p>
		@endif
	@elseif($status == 2)
		<b>Something went wrong</b>
		<p> We found some unexpected errors<p>
		<p>
			{{(!empty($exception))?$exception:''}}
		</p>
	@endif
	@if(!empty($data) && $status != 2)
		<b>Some rows are ignored</b>
		<p><br/>Errors</p>
		<table border=0>
			@foreach($data as $key=>$msg)
				<tr><td>{{$msg}}</td></tr>
			@endforeach
		</table>
	@endif
	
</body>
</html>