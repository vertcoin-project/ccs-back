XMR {{$amount_received}} /  XMR {{$project->target_amount}} Target

{{$contributions}} contributions made.  {{$percentage}}%
<br>

{!! QrCode::size(400)->generate($project->uri); !!}