<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Usuarios</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px; border: 1px solid #000; font-size: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Reporte de Usuarios</h2>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Nombre Completo</th>
                <th>Teléfono</th>
                <th>CI</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ optional($u->role)->descripcion ?? 'Sin rol' }}</td>
                    <td>
                        {{ optional($u->persona)->nombres }}
                        {{ optional($u->persona)->apellido_paterno }}
                        {{ optional($u->persona)->apellido_materno }}
                    </td>
                    <td>{{ optional($u->persona)->telefono }}</td>
                    <td>{{ optional($u->persona)->ci }}</td>
                    <td>{{ $u->estado ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ $u->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
