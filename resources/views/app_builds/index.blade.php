<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 APK Feltöltő / Build Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">

<div class="container">
    <h2 class="mb-4">📱 APK Feltöltő / Build Manager</h2>

    {{-- Flash üzenet --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Feltöltő űrlap --}}
    <form action="{{ route('builds.store') }}" method="POST" enctype="multipart/form-data" class="mb-5">
        @csrf
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">APK fájl</label>
                <input type="file" name="apk" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Verzió</label>
                <input type="text" name="version" class="form-control" placeholder="1.0.0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Típus</label>
                <select name="build_type" class="form-select">
                    <option value="release">Release</option>
                    <option value="debug">Debug</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Megjegyzés</label>
                <input type="text" name="notes" class="form-control" placeholder="pl. új verzió / hotfix">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-dark w-100">Feltöltés</button>
            </div>
        </div>
    </form>

    {{-- APK lista --}}
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Fájlnév</th>
                    <th>Verzió</th>
                    <th>Típus</th>
                    <th>Megjegyzés</th>
                    <th>Feltöltve</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($builds as $build)
                    <tr>
                        <td>{{ $build->id }}</td>
                        <td>{{ $build->original_name }}</td>
                        <td>{{ $build->version ?? '-' }}</td>
                        <td>{{ ucfirst($build->build_type) }}</td>
                        <td>{{ $build->notes ?? '-' }}</td>
                        <td>{{ $build->created_at->format('Y.m.d H:i') }}</td>
                        <td>
                            <a href="{{ route('builds.download', $build) }}" class="btn btn-sm btn-success">⬇</a>
                            <form action="{{ route('builds.destroy', $build) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Törlöd?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Nincs még feltöltött build.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
