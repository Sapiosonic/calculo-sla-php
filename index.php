<?php
function dataSemana($data)
{
    $data_semana = [
        'Monday' => 'seg',
        'Tuesday' => 'ter',
        'Wednesday' => 'qua',
        'Thursday' => 'qui',
        'Friday' => 'sex',
        'Saturday' => 'sab',
        'Sunday' => 'dom'
    ];

    $timestamp = strtotime($data);

    return $data_semana[date('l', $timestamp)];
}

function calcularTempoTrabalhadoNoDia($data, $horaInicio, $horaFim)
{
    $dataHoraInicio = new DateTime("$data $horaInicio:00");
    $dataHoraFim = new DateTime("$data $horaFim:00");
    $agora = new DateTime();

    $intervalo = $dataHoraInicio->diff(min($dataHoraFim, $agora));

    return $intervalo->h + ($intervalo->i / 60) + ($intervalo->s / 3600); 
}

$horarios = [
    'seg' => [strtotime('08:00'), strtotime('17:00')],
    'ter' => [strtotime('08:00'), strtotime('17:00')],
    'qua' => [strtotime('08:00'), strtotime('17:00')],
    'qui' => [strtotime('08:00'), strtotime('17:00')],
    'sex' => [strtotime('08:00'), strtotime('17:00')],
    'sab' => null, // Sem expediente
    'dom' => null  // Sem expediente
];

function horasUteisPorDia($horarios)
{
    $horasUteis = 0;

    foreach ($horarios as $dia => $horario) {
        if ($horario !== null) {
            $horaInicio = date('H', $horario[0]);
            $horaFim = date('H', $horario[1]);
            $horasUteis += $horaFim - $horaInicio;
        }
    }

    return $horasUteis;
}


function encontrarProximoDiaUtilEHorario($data, $horarios)
{
    $dataAtual = new DateTime($data);
    $diaAtual = dataSemana($data);

    while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null) {
        $dataAtual->modify('+1 day');
        $diaAtual = dataSemana($dataAtual->format('Y-m-d'));
    }

    return $dataAtual;
}

// function calcularConclusaoSLA($dataInicioSLA, $sla, $horarios)
// {
//     $data = new DateTime();
//     $data->setTimestamp($dataInicioSLA);

//     $diaAtual = dataSemana($data->format('Y-m-d'));

//     while ($sla > 0) {
//         // Se o dia não tiver expediente, avança para o próximo dia útil
//         while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null) {
//             $data->modify('+1 day');
//             $diaAtual = dataSemana($data->format('Y-m-d'));
//         }

//         $horaInicioExpediente = date('H', $horarios[$diaAtual][0]);
//         $horaFimExpediente = date('H', $horarios[$diaAtual][1]);

//         // Calcula o tempo disponível no dia
//         $tempoDisponivelNoDia = $horaFimExpediente - max($horaInicioExpediente, $data->format('H'));

//         // Calcula o tempo trabalhado no dia
//         $tempoTrabalhadoNoDia = min($sla, $tempoDisponivelNoDia);

//         // Adiciona as horas restantes
//         $data->setTime($horaInicioExpediente + $tempoTrabalhadoNoDia, 0);

//         // Subtrai o tempo trabalhado do SLA restante
//         $sla -= $tempoTrabalhadoNoDia;

//         // Avança para o próximo dia
//         if($data->format('H') >= $horaFimExpediente) {
//             var_dump($data);
//             $data->modify('+1 day');
//         }else{
//             continue;
//         }
//     }
//     return $data->format('Y-m-d H:i:s');  
// }

// function calcularConclusaoSLA($dataInicioSLA, $sla, $horarios)
// {
//     $data = new DateTime();
//     $data->setTimestamp($dataInicioSLA);

//     $diaAtual = dataSemana($data->format('Y-m-d'));

//     while ($sla > 0) {
//         // Se o dia não tiver expediente, avança para o próximo dia útil
//         while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null) {
//             $data->modify('+1 day');
//             $diaAtual = dataSemana($data->format('Y-m-d'));
//         }

//         $horaInicioExpediente = date('H', $horarios[$diaAtual][0]);
//         $horaFimExpediente = date('H', $horarios[$diaAtual][1]);

//         // Calcula o tempo disponível no dia
//         $tempoDisponivelNoDia = $horaFimExpediente - max($horaInicioExpediente, $data->format('H'));

//         // Calcula o tempo trabalhado no dia
//         $tempoTrabalhadoNoDia = min($sla, $tempoDisponivelNoDia);

//         // Adiciona as horas restantes
//         $data->setTime($horaInicioExpediente + $tempoTrabalhadoNoDia, 0);

//         // Subtrai o tempo trabalhado do SLA restante
//         $sla -= $tempoTrabalhadoNoDia;

//         // Verifica se atingiu o final do expediente e o SLA ainda não foi concluído
//         if ($data->format('H') >= $horaFimExpediente && $sla > 0) {
//             // Salva a data atual antes do ajuste
//             $dataAntesDoAjuste = clone $data;

//             // Inicializa a próxima data útil
//             $proximoDiaUtil = clone $data;

//             // Ajusta para o início do expediente do próximo dia útil
//             do {
//                 $proximoDiaUtil->modify('+1 day');
//                 $diaAtual = dataSemana($proximoDiaUtil->format('Y-m-d'));
//             } while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null);

//             $horaInicioProximoDia = date('H', $horarios[$diaAtual][0]);

//             // Ajusta para o início do expediente do próximo dia útil
//             $proximoDiaUtil->setTime($horaInicioProximoDia, 0);

//             // Desconta as horas desde o momento antes do ajuste
//             $sla -= $dataAntesDoAjuste->diff($proximoDiaUtil)->h;

//             // Desconta as horas do próximo dia útil
//             $sla -= horasUteisPorDia($horarios);

//             // Atualiza a data para o início do expediente do próximo dia útil
//             $data = $proximoDiaUtil;
//         }

//         // Atualiza o dia atual
//         $diaAtual = dataSemana($data->format('Y-m-d'));
//     }

//     return $data->format('Y-m-d H:i:s');
// }


// // Exemplo de uso:
// $dataInicioSLAExemplo = strtotime('2024-01-22 08:00:00');
// $slaExemplo = 11;


// $dataConclusaoSLA = calcularConclusaoSLA($dataInicioSLAExemplo, $slaExemplo, $horarios);
// echo "Data de conclusão do SLA: $dataConclusaoSLA\n";

function calcularConclusaoSLA($dataInicioSLA, $sla, $horarios)
{
    $data = new DateTime();
    $data->setTimestamp($dataInicioSLA);

    $diaAtual = dataSemana($data->format('Y-m-d'));

    while ($sla > 0) {
        // Se o dia não tiver expediente, avança para o próximo dia útil
        while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null) {
            $data->modify('+1 day');
            $diaAtual = dataSemana($data->format('Y-m-d'));
        }

        $horaInicioExpediente = date('H', $horarios[$diaAtual][0]);
        $horaFimExpediente = date('H', $horarios[$diaAtual][1]);

        // Calcula o tempo disponível no dia
        $tempoDisponivelNoDia = $horaFimExpediente - max($horaInicioExpediente, $data->format('H'));

        // Calcula o tempo trabalhado no dia
        $tempoTrabalhadoNoDia = min($sla, $tempoDisponivelNoDia);

        // Adiciona as horas trabalhadas
        $data->modify("+{$tempoTrabalhadoNoDia} hours");

        // Subtrai o tempo trabalhado do SLA restante
        $sla -= $tempoTrabalhadoNoDia;

        // Verifica se atingiu o final do expediente e o SLA ainda não foi concluído
        if ($data->format('H') >= $horaFimExpediente && $sla > 0) {
            // Salva a data atual antes do ajuste
            $dataAntesDoAjuste = clone $data;

            // Inicializa a próxima data útil
            $proximoDiaUtil = clone $data;

            // Ajusta para o início do expediente do próximo dia útil
            do {
                $proximoDiaUtil->modify('+1 day');
                $diaAtual = dataSemana($proximoDiaUtil->format('Y-m-d'));
            } while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null);

            $horaInicioProximoDia = date('H', $horarios[$diaAtual][0]);

            // Ajusta para o início do expediente do próximo dia útil
            $proximoDiaUtil->setTime($horaInicioProximoDia, 0);

            // Atualiza a data para o início do expediente do próximo dia útil
            $data = $proximoDiaUtil;
        }

        // Atualiza o dia atual
        $diaAtual = dataSemana($data->format('Y-m-d'));
    }

    return $data->format('Y-m-d H:i:s');
}

// Exemplo de uso:
$dataInicioSLAExemplo = strtotime('2024-01-22 08:00:00');
$slaExemplo = 50;

$dataConclusaoSLA = calcularConclusaoSLA($dataInicioSLAExemplo, $slaExemplo, $horarios);
echo "Data de conclusão do SLA: $dataConclusaoSLA\n";



















