<table>
    <thead class="flip-content">
    <tr>
        <th>Payment ID</th>
        <th>Status</th>
        <th>Funding Received</th>
        <th>Required</th>
    </tr>
    </thead>
    <tbody>
@foreach ($projects as $project)
    <tr>
        <td><a href='{!! url('/projects/'.$project->payment_id); !!}'>{{ $project->payment_id }}</a></td>
        <td>{{$project->status}}</td>
        <td>{{$project->amountReceived}} XMR</td>
        <td>{{$project->target_amount}} XMR</td>
    </tr>
@endforeach
    </tbody>
</table>