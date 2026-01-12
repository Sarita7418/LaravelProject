<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClasificacionesLinameSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Definimos los Grupos Principales (Nivel 1)
        // La clave del array es el código ('A') para buscarlo fácil luego
        $grupos = [
            'A' => 'TRACTO ALIMENTARIO Y METABOLISMO',
            'B' => 'SANGRE Y ÓRGANOS FORMADORES DE SANGRE',
            'C' => 'SISTEMA CARDIOVASCULAR',
            'D' => 'DERMATOLÓGICOS',
            'G' => 'SISTEMA GENITOURINARIO Y HORMONAS SEXUALES',
            'H' => 'PREPARADOS HORMONALES SISTÉMICOS, EXCL. HORMONAS SEXUALES E INSULINAS',
            'J' => 'ANTIINFECCIOSOS PARA USO SISTÉMICO',
            'L' => 'AGENTES ANTINEOPLÁSICOS E INMUNOMODULADORES',
            'M' => 'SISTEMA MUSCULOESQUELÉTICO',
            'N' => 'SISTEMA NERVIOSO',
            'P' => 'PRODUCTOS ANTIPARASITARIOS, INSECTICIDAS Y REPELENTES',
            'R' => 'SISTEMA RESPIRATORIO',
            'S' => 'ÓRGANOS DE LOS SENTIDOS',
            'V' => 'VARIOS',
        ];

        // Insertamos Grupos y guardamos sus IDs en un mapa temporal
        $mapaIds = [];
        foreach ($grupos as $codigo => $nombre) {
            $id = DB::table('clasificaciones_liname')->insertGetId([
                'nivel' => 1,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'padre_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $mapaIds[$codigo] = $id;
        }

        // 2. Definimos los Subgrupos (Nivel 2)
        // Formato: ['Padre', 'CodigoHijo', 'NombreHijo']
        $subgrupos = [
            // Grupo A
            ['A', '01', 'PREPARADOS ESTOMATOLÓGICOS'],
            ['A', '02', 'AGENTES PARA EL TRATAMIENTO DE ALTERACIONES CAUSADAS POR ÁCIDOS'],
            ['A', '03', 'AGENTES CONTRA PADECIMIENTOS FUNCIONALES DEL ESTÓMAGO E INTESTINO'],
            ['A', '04', 'ANTIEMÉTICOS Y ANTINAUSEOSOS'],
            ['A', '06', 'LAXANTES'],
            ['A', '07', 'ANTIDIARREICOS, AGENTES ANTIINFLAMATORIOS/ANTIINFECCIOSOS INTESTINALES'],
            ['A', '09', 'DIGESTIVOS, INCLUYENDO ENZIMAS'],
            ['A', '10', 'DROGAS USADAS EN DIABETES'],
            ['A', '11', 'VITAMINAS'],
            ['A', '12', 'SUPLEMENTOS MINERALES'],
            // Grupo B
            ['B', '01', 'AGENTES ANTITROMBÓTICOS'],
            ['B', '02', 'ANTIHEMORRÁGICOS'],
            ['B', '03', 'PREPARADOS ANTIANÉMICOS'],
            ['B', '05', 'SUSTITUTOS DE LA SANGRE Y SOLUCIONES PARA PERFUSIÓN'],
            // Grupo C
            ['C', '01', 'TERAPIA CARDÍACA'],
            ['C', '02', 'ANTIHIPERTENSIVOS'],
            ['C', '03', 'DIURÉTICOS'],
            ['C', '05', 'VASOPROTECTORES'],
            ['C', '07', 'AGENTES BETA-BLOQUEANTES'],
            ['C', '08', 'BLOQUEANTES DE CANALES DE CALCIO'],
            ['C', '09', 'AGENTES QUE ACTÚAN SOBRE EL SISTEMA RENINA-ANGIOTENSINA'],
            ['C', '10', 'AGENTES QUE REDUCEN LOS LÍPIDOS SÉRICOS'],
            // Grupo D
            ['D', '01', 'ANTIFÚNGICOS PARA USO DERMATOLÓGICO'],
            ['D', '02', 'EMOLIENTES Y PROTECTORES'],
            ['D', '04', 'ANTIPRURIGINOSOS, INCL. ANTIHISTAMÍNICOS, ANESTÉSICOS, ETC.'],
            ['D', '06', 'ANTIBIÓTICOS Y QUIMIOTERÁPICOS PARA USO DERMATOLÓGICO'],
            ['D', '07', 'PREPARADOS DERMATOLÓGICOS CON CORTICOESTEROIDES'],
            ['D', '08', 'ANTISÉPTICOS Y DESINFECTANTES'],
            ['D', '10', 'PREPARADOS ANTI-ACNÉ'],
            ['D', '11', 'OTROS PREPARADOS DERMATOLÓGICOS'],
            // Grupo G
            ['G', '01', 'ANTIINFECCIOSOS Y ANTISÉPTICOS GINECOLÓGICOS'],
            ['G', '02', 'OTROS GINECOLÓGICOS'],
            ['G', '03', 'HORMONAS SEXUALES Y MODULADORES DEL SISTEMA GENITAL'],
            ['G', '04', 'PRODUCTOS DE USO UROLÓGICO'],
            // Grupo H
            ['H', '01', 'HORMONAS HIPOFISARIAS E HIPOTALÁMICAS Y SUS ANÁLOGOS'],
            ['H', '02', 'CORTICOESTEROIDES PARA USO SISTÉMICO'],
            ['H', '03', 'TERAPIA TIRÓIDEA'],
            // Grupo J
            ['J', '01', 'ANTIBACTERIANOS PARA USO SISTÉMICO'],
            ['J', '02', 'ANTIMICÓTICOS PARA USO SISTÉMICO'],
            ['J', '04', 'ANTIMICOBACTERIAS'],
            ['J', '05', 'ANTIVIRALES DE USO SISTÉMICO'],
            ['J', '06', 'SUEROS INMUNES E INMUNOGLOBULINAS'],
            ['J', '07', 'VACUNAS'],
            // Grupo L
            ['L', '01', 'AGENTES ANTINEOPLÁSICOS'],
            ['L', '02', 'TERAPIA ENDÓCRINA'],
            ['L', '03', 'INMUNOESTIMULANTES'],
            ['L', '04', 'AGENTES INMUNOSUPRESORES'],
            // Grupo M
            ['M', '01', 'PRODUCTOS ANTIINFLAMATORIOS Y ANTIRREUMÁTICOS'],
            ['M', '03', 'RELAJANTES MUSCULARES'],
            ['M', '04', 'PREPARADOS ANTIGOTOSOS'],
            ['M', '05', 'DROGAS PARA EL TRATAMIENTO DE ENFERMEDADES ÓSEAS'],
            // Grupo N
            ['N', '01', 'ANESTÉSICOS'],
            ['N', '02', 'ANALGÉSICOS'],
            ['N', '03', 'ANTIEPILÉPTICOS'],
            ['N', '04', 'ANTIPARKINSONIANOS'],
            ['N', '05', 'PSICOLÉPTICOS'],
            ['N', '06', 'PSICOANALÉPTICOS'],
            ['N', '07', 'OTRAS DROGAS QUE ACTÚAN SOBRE EL SISTEMA NERVIOSO'],
            // Grupo P
            ['P', '01', 'ANTIPROTOZOARIOS'],
            ['P', '02', 'ANTIHELMÍNTICOS'],
            ['P', '03', 'ECTOPARASITICIDAS, INCL. ESCABICIDAS, INSECTICIDAS Y REPELENTES'],
            // Grupo R
            ['R', '03', 'AGENTES CONTRA PADECIMIENTOS OBSTRUCTIVOS DE LAS VÍAS RESPIRATORIAS'],
            ['R', '05', 'PREPARADOS PARA LA TOS Y EL RESFRÍO'],
            ['R', '06', 'ANTIHISTAMÍNICOS PARA USO SISTÉMICO'],
            ['R', '07', 'OTROS PRODUCTOS PARA EL SISTEMA RESPIRATORIO'],
            // Grupo S
            ['S', '01', 'OFTALMOLÓGICOS'],
            ['S', '02', 'OTOLÓGICOS'],
            // Grupo V
            ['V', '03', 'TODO EL RESTO DE LOS PRODUCTOS TERAPÉUTICOS'],
            ['V', '06', 'NUTRIENTES GENERALES'],
            ['V', '08', 'MEDIOS DE CONTRASTE'],
        ];

        foreach ($subgrupos as $sub) {
            $padreLetra = $sub[0];
            
            // Verificamos si existe el padre (por seguridad)
            if (isset($mapaIds[$padreLetra])) {
                DB::table('clasificaciones_liname')->insert([
                    'nivel' => 2,
                    'codigo' => $sub[1],
                    'nombre' => $sub[2],
                    'padre_id' => $mapaIds[$padreLetra], // Usamos el ID real que se creó arriba
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}