<?php

$horarios = [
    'seg' => [strtotime('08:00'), strtotime('17:00')],
    'ter' => [strtotime('08:00'), strtotime('17:00')],
    'qua' => [strtotime('08:00'), strtotime('17:00')],
    'qui' => [strtotime('08:00'), strtotime('17:00')],
    'sex' => [strtotime('08:00'), strtotime('17:00')],
    'sab' => null, // Sem expediente
    'dom' => null  // Sem expediente
];

$feriados = [
    '2024-01-01',
    '2024-01-25',
    '2024-02-12',
    '2024-02-13',
    '2024-03-29',
    '2024-04-21',
    '2024-05-01',
    '2024-05-30',
    '2024-09-07',
    '2024-10-12',
    '2024-11-02',
    '2024-11-15',
    '2024-11-20',
    '2024-12-25'
];

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

function calcularConclusaoSLA($dataInicioSLA, $sla, $horarios, $feriados)
{
    $data = new DateTime();
    $data->setTimestamp($dataInicioSLA);

    $diaAtual = dataSemana($data->format('Y-m-d'));

    while ($sla > 0) {
        // Se o dia não tiver expediente ou for feriado, avança para o próximo dia útil
        while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null || in_array($data->format('Y-m-d'), $feriados)) {
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
            } while (!isset($horarios[$diaAtual]) || $horarios[$diaAtual] === null || in_array($proximoDiaUtil->format('Y-m-d'), $feriados));

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
$slaExemplo = 27;

$dataConclusaoSLA = calcularConclusaoSLA($dataInicioSLAExemplo, $slaExemplo, $horarios, $feriados);
echo "Data de conclusão do SLA: $dataConclusaoSLA\n";






















