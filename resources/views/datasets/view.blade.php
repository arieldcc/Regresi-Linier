@extends('layouts.admin.main')
@section('title', 'Show Dataset')

@section('content')
<main class="content-wrapper container">
    <h4>Edit Dataset: {{ $dataset->name }}</h4>

    <form action="{{ route('dataset.updateExcel', $dataset->id) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="table table-bordered">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th
                            @if (in_array($header, $xVars)) style="background-color:#d1e7dd"
                            @elseif (in_array($header, $yVars)) style="background-color:#cff4fc"
                            @endif
                        >
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr>
                        @foreach ($row as $j => $value)
                            <td>
                                <input
                                    type="text"
                                    name="data[{{ $loop->parent->index }}][{{ $loop->index }}]"
                                    value="{{ $value }}"
                                    class="form-control"
                                />
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
    </form>

    <div class="mt-4">
        <p><span style="background-color:#d1e7dd; padding:2px 5px">Hijau</span> = Variabel X</p>
        <p><span style="background-color:#cff4fc; padding:2px 5px">Biru Muda</span> = Variabel Y</p>
    </div>

    <a href="{{ route('dataset.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</main>
@endsection
