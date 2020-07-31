<?php

namespace JonatasFenske\Correios;

/**
 * Description of Config
 *
 * @author Jônatas Fenske
 */
abstract class Config {
    /*     * ****************
     * WEBSERVICE CONFIG
     * **************** */

    /**
     * URL do SIGEP webservice dos Correios.
     */
    const WEBSERVICE_SIGEP = 'https://apps.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente';

    /**
     * URL do webservice dos Correios para calculo de preços e prazos.
     */
    const WEBSERVICE_CALC_PRICE = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo';

    /**
     * URL do webservice dos Correios para calculo de preços e prazos.
     */
    const WEBSERVICE_TRACKING = 'http://www.linkcorreios.com.br';


    /*     * ****************
     * PACKAGE CONFIG
     * **************** */

    /**
     * Formatos caixa ou pacote.
     */
    const PACKAGE_BOX = 1;

    /**
     * Formatos rolo ou prisma.
     */
    const PACKAGE_ROLL = 2;

    /**
     * Formato envelope.
     */
    const PACKAGE_ENVELOPE = 3;


    /*     * ****************
     * SERVICES CONFIG
     * **************** */

    /**
     * PAC.
     */
    const SERVICE_PAC = '4510';

    /**
     * PAC com contrato.
     */
    const SERVICE_PAC_CONTRATO = '4669';

    /**
     * Sedex.
     */
    const SERVICE_SEDEX = '4014';

    /**
     * Sedex com contrato.
     */
    const SERVICE_SEDEX_CONTRATO = '4162';

    /**
     * Sedex a Cobrar.
     */
    const SERVICE_SEDEX_A_COBRAR = '40045';

    /**
     * Sedex 10.
     */
    const SERVICE_SEDEX_10 = '40215';

    /**
     * Sedex Hoje.
     */
    const SERVICE_SEDEX_HOJE = '40290';

    /**
     * Sedex Contrato 04316
     */
    const SERVICE_SEDEX_CONTRATO_04316 = '4316';

    /**
     * Sedex Contrato 40096
     */
    const SERVICE_SEDEX_CONTRATO_40096 = '40096';

    /**
     * Sedex Contrato 40436
     */
    const SERVICE_SEDEX_CONTRATO_40436 = '40436';

    /**
     * Sedex Contrato 40444
     */
    const SERVICE_SEDEX_CONTRATO_40444 = '40444';

    /**
     * Sedex Contrato 40568
     */
    const SERVICE_SEDEX_CONTRATO_40568 = '40568';

    /**
     * PAC Contrato 04812
     */
    const SERVICE_PAC_CONTRATO_04812 = '4812';

    /**
     * PAC Contrato 41068
     */
    const SERVICE_PAC_CONTRATO_41068 = '41068';

    /**
     * PAC Contrato 41211
     */
    const SERVICE_PAC_CONTRATO_41211 = '41211';

}
