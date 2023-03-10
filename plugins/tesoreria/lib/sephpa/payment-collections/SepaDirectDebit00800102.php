<?php
/**
 * Sephpa
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2015 Alexander Schickedanz
 * @link      https://github.com/AbcAeffchen/Sephpa
 *
 * @author  Alexander Schickedanz <abcaeffchen@gmail.com>
 */

namespace AbcAeffchen\Sephpa;
use AbcAeffchen\SepaUtilities\SepaUtilities;

/**
 * Manages direct debits
 */
class SepaDirectDebit00800102 implements SepaPaymentCollection
{
    /**
     * @var string CCY Default currency
     */
    const CCY = 'EUR';
    /**
     * @type int VERSION The SEPA file version of this collection
     */
    const VERSION = SepaUtilities::SEPA_PAIN_008_001_02;
    /**
     * @type bool $sanitizeFlags
     */
    private $checkAndSanitize = true;
    /**
     * @type int $sanitizeFlags
     */
    private $sanitizeFlags = 0;
    /**
     * @var mixed[] $payments Saves all payments
     */
    private $payments = array();
    /**
     * @var mixed[] $debitInfo Saves the transfer information for the collection.
     */
    private $debitInfo;
    /**
     * @type string $dbtrIban The IBAN of the creditor
     */
    private $cdtrIban;

    private $today;

    /**
     * @param mixed[] $debitInfo        Needed keys: 'pmtInfId', 'lclInstrm', 'seqTp', 'cdtr',
     *                                  'iban', 'bic'; optional keys: 'ccy', 'btchBookg',
     *                                  'ctgyPurp', 'ultmtCdtr', 'reqdColltnDt', 'ci'
     * @param bool    $checkAndSanitize All inputs will be checked and sanitized before creating
     *                                  the collection. If you check the inputs yourself you can
     *                                  set this to false.
     * @param int     $flags            The flags used for sanitizing
     * @throws SephpaInputException
     */
    public function __construct(array $debitInfo, $checkAndSanitize = true, $flags = 0)
    {
        $this->today = (int) date('Ymd');
        $this->checkAndSanitize = $checkAndSanitize;
        $this->sanitizeFlags = $flags;

        if($this->checkAndSanitize)
        {
            if(!SepaUtilities::checkRequiredCollectionKeys($debitInfo, self::VERSION) )
                throw new SephpaInputException('One of the required inputs \'pmtInfId\', \'lclInstrm\', \'seqTp\', \'cdtr\', \'iban\', \'bic\' is missing.');

            $checkResult = SepaUtilities::checkAndSanitizeAll($debitInfo, $this->sanitizeFlags, array('version' => self::VERSION));

            if($checkResult !== true)
                throw new SephpaInputException('The values of ' . $checkResult . ' are invalid.');

            // IBAN and BIC can belong to each other?
            if(!empty($transferInfo['bic']) && !SepaUtilities::crossCheckIbanBic($transferInfo['iban'],$transferInfo['bic']))
                throw new SephpaInputException('IBAN and BIC do not belong to each other.');
        }

        $this->debitInfo = $debitInfo;
    }

    /**
     * calculates the sum of all payments in this collection
     *
     * @param mixed[] $paymentInfo needed keys: 'pmtId', 'instdAmt', 'mndtId', 'dtOfSgntr', 'bic',
     *                             'dbtr', 'iban';
     *                             optional keys: 'amdmntInd', 'orgnlMndtId', 'orgnlCdtrSchmeId_nm',
     *                             'orgnlCdtrSchmeId_id', 'orgnlDbtrAcct_iban', 'orgnlDbtrAgt',
     *                             'elctrncSgntr', 'ultmtDbtr', 'purp', 'rmtInf'
     * @throws SephpaInputException
     * @return void
     */
    public function addPayment(array $paymentInfo)
    {
        if($this->checkAndSanitize)
        {
            if(!SepaUtilities::checkRequiredPaymentKeys($paymentInfo, self::VERSION) )
                throw new SephpaInputException('One of the required inputs \'pmtId\', \'instdAmt\', \'mndtId\', \'dtOfSgntr\', \'dbtr\', \'iban\' is missing.');

            $bicRequired = (!SepaUtilities::isNationalTransaction($this->cdtrIban,$paymentInfo['iban']) && $this->today <= SepaUtilities::BIC_REQUIRED_THRESHOLD);

            $checkResult = SepaUtilities::checkAndSanitizeAll($paymentInfo, $this->sanitizeFlags,array('allowEmptyBic' => $bicRequired, 'version' => self::VERSION));

            if($checkResult !== true)
                throw new SephpaInputException('The values of ' . $checkResult . ' are invalid.');

            if( !empty( $paymentInfo['amdmntInd'] ) && $paymentInfo['amdmntInd'] === 'true' )
            {

                if( SepaUtilities::containsNotAnyKey($paymentInfo, array('orgnlMndtId',
                                                                         'orgnlCdtrSchmeId_nm',
                                                                         'orgnlCdtrSchmeId_id',
                                                                         'orgnlDbtrAcct_iban',
                                                                         'orgnlDbtrAgt'))
                )
                    throw new SephpaInputException('You set \'amdmntInd\' to \'true\', so you have to set also at least one of the following inputs: \'orgnlMndtId\', \'orgnlCdtrSchmeId_nm\', \'orgnlCdtrSchmeId_id\', \'orgnlDbtrAcct_iban\', \'orgnlDbtrAgt\'.');

                if( !empty( $paymentInfo['orgnlDbtrAgt'] ) && $paymentInfo['orgnlDbtrAgt'] === 'SMNDA' && $this->debitInfo['seqTp'] !== 'FRST' )
                    throw new SephpaInputException('You set \'amdmntInd\' to \'true\' and \'orgnlDbtrAgt\' to \'SMNDA\', \'seqTp\' has to be \'FRST\'.');

            }
            else
            {
                $paymentInfo['amdmntInd'] = 'false';
            }

            // IBAN and BIC can belong to each other?
            if(!empty($paymentInfo['bic']) && !SepaUtilities::crossCheckIbanBic($paymentInfo['iban'],$paymentInfo['bic']))
                throw new SephpaInputException('IBAN and BIC do not belong to each other.');
        }

        $this->payments[] = $paymentInfo;
    }

    /**
     * Calculates the sum of all payments in this collection
     * @return float
     */
    public function getCtrlSum()
    {
        $sum = 0;
        foreach($this->payments as $payment){
            $sum += $payment['instdAmt'];
        }
        return $sum;
    }

    /**
     * Counts the payments in this collection
     * @return int
     */
    public function getNumberOfTransactions()
    {
        return count($this->payments);
    }

    /**
     * Generates the xml for the collection using generatePaymentXml
     *
     * @param \SimpleXMLElement $pmtInf The PmtInf-Child of the xml object
     * @return void
     */
    public function generateCollectionXml(\SimpleXMLElement $pmtInf)
    {
        $ccy = ( empty( $this->debitInfo['ccy'] ) ) ? self::CCY : $this->debitInfo['ccy'];

        $datetime     = new \DateTime();
        $reqdColltnDt = ( !empty( $this->debitInfo['reqdColltnDt'] ) )
            ? $this->debitInfo['reqdColltnDt'] : $datetime->format('Y-m-d');

        $pmtInf->addChild('PmtInfId', $this->debitInfo['pmtInfId']);
        $pmtInf->addChild('PmtMtd', 'DD');
        if( !empty( $this->debitInfo['btchBookg'] ) )
            $pmtInf->addChild('BtchBookg', $this->debitInfo['btchBookg']);
        $pmtInf->addChild('NbOfTxs', $this->getNumberOfTransactions());
        $pmtInf->addChild('CtrlSum', sprintf("%01.2f", $this->getCtrlSum()));

        $pmtTpInf = $pmtInf->addChild('PmtTpInf');
        $pmtTpInf->addChild('SvcLvl')->addChild('Cd', 'SEPA');
        $pmtTpInf->addChild('LclInstrm')->addChild('Cd', $this->debitInfo['lclInstrm']);
        $pmtTpInf->addChild('SeqTp', $this->debitInfo['seqTp']);
        if( !empty( $this->debitInfo['ctgyPurp'] ) )
            $pmtTpInf->addChild('CtgyPurp')->addChild('Cd', $this->debitInfo['ctgyPurp']);

        $pmtInf->addChild('ReqdColltnDt', $reqdColltnDt);
        $pmtInf->addChild('Cdtr')->addChild('Nm', $this->debitInfo['cdtr']);

        $cdtrAcct = $pmtInf->addChild('CdtrAcct');
        $cdtrAcct->addChild('Id')->addChild('IBAN', $this->debitInfo['iban']);
        //$cdtrAcct->addChild('Ccy', $ccy);

        if( !empty( $this->debitInfo['bic'] ) )
            $pmtInf->addChild('CdtrAgt')->addChild('FinInstnId')
                   ->addChild('BIC', $this->debitInfo['bic']);
        else
            $pmtInf->addChild('CdtrAgt')->addChild('FinInstnId')->addChild('Othr')
                   ->addChild('Id', 'NOTPROVIDED');

        if( !empty( $this->debitInfo['ultmtCdtr'] ) )
            $pmtInf->addChild('UltmtCdtr')->addChild('Nm', $this->debitInfo['ultmtCdtr']);

        $pmtInf->addChild('ChrgBr', 'SLEV');

        $CdtrSchmeId = $pmtInf->addChild('CdtrSchmeId')->addChild('Id')->addChild('PrvtId')->addChild('Othr');
        $CdtrSchmeId->addChild('Id', $this->debitInfo['ci']);
        $CdtrSchmeId->addChild('SchmeNm')->addChild('Prtry', 'SEPA');

        foreach($this->payments as $payment)
        {
            $drctDbtTxInf = $pmtInf->addChild('DrctDbtTxInf');
            $this->generatePaymentXml($drctDbtTxInf, $payment, $ccy);
        }
    }

    /**
     * Generates the xml for a single payment
     *
     * @param \SimpleXMLElement $drctDbtTxInf
     * @param mixed[]           $payment One of the payments in $this->payments
     * @param string            $ccy     currency
     * @return void
     */
    private function generatePaymentXml(\SimpleXMLElement $drctDbtTxInf, $payment, $ccy)
    {
        $drctDbtTxInf->addChild('PmtId')->addChild('EndToEndId', $payment['pmtId']);
        $drctDbtTxInf->addChild('InstdAmt', sprintf("%01.2f", $payment['instdAmt']))
                     ->addAttribute('Ccy', $ccy);

        $mndtRltdInf = $drctDbtTxInf->addChild('DrctDbtTx')->addChild('MndtRltdInf');
        $mndtRltdInf->addChild('MndtId', $payment['mndtId']);
        $mndtRltdInf->addChild('DtOfSgntr', $payment['dtOfSgntr']);

         if( isset($payment['amdmntInd']) )
         {
           $mndtRltdInf->addChild('AmdmntInd', $payment['amdmntInd']);
           if( $payment['amdmntInd'] === 'true' )
           {
               $amdmntInd = $mndtRltdInf->addChild('AmdmntInfDtls');
               if( !empty( $payment['orgnlMndtId'] ) )
                   $amdmntInd->addChild('OrgnlMndtId', $payment['orgnlMndtId']);
               if( !empty( $payment['orgnlCdtrSchmeId_Nm'] ) || !empty( $payment['orgnlCdtrSchmeId_Nm'] ) )
               {
                   $orgnlCdtrSchmeId = $amdmntInd->addChild('OrgnlCdtrSchmeId');
                   if( !empty( $payment['orgnlCdtrSchmeId_Nm'] ) )
                       $orgnlCdtrSchmeId->addChild('Nm', $payment['orgnlCdtrSchmeId_Nm']);
                   if( !empty( $payment['orgnlCdtrSchmeId_Id'] ) )
                   {
                       $othr = $orgnlCdtrSchmeId->addChild('Id')->addChild('PrvtId')
                                                ->addChild('Othr');
                       $othr->addChild('Id', $payment['orgnlCdtrSchmeId_Id']);
                       $othr->addChild('SchmeNm')->addChild('Prtry', 'SEPA');
                   }
               }
               if( !empty( $payment['orgnlDbtrAcct_iban'] ) )
                   $amdmntInd->addChild('OrgnlDbtrAcct')->addChild('Id')
                             ->addChild('IBAN', $payment['orgnlDbtrAcct_iban']);
               if( !empty( $payment['orgnlDbtrAgt'] ) )
                   $amdmntInd->addChild('OrgnlDbtrAgt')->addChild('FinInstnId')->addChild('Othr')
                             ->addChild('Id', 'SMNDA');
           }
        }
        if( !empty( $payment['elctrncSgntr'] ) )
            $mndtRltdInf->addChild('ElctrncSgntr', $payment['elctrncSgntr']);

        if( !empty( $payment['bic'] ) )
            $drctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')
                   ->addChild('BIC', $payment['bic']);
        else
            $drctDbtTxInf->addChild('DbtrAgt')->addChild('FinInstnId')->addChild('Othr')
                   ->addChild('Id', 'NOTPROVIDED');

        $drctDbtTxInf->addChild('Dbtr')->addChild('Nm', $payment['dbtr']);
        $drctDbtTxInf->addChild('DbtrAcct')->addChild('Id')
                     ->addChild('IBAN', $payment['iban']);
        if( !empty( $payment['ultmtDbtr'] ) )
            $drctDbtTxInf->addChild('UltmtDbtr')->addChild('Nm', $payment['ultmtDbtr']);
        if( !empty( $payment['purp'] ) )
            $drctDbtTxInf->addChild('Purp')->addChild('Cd', $payment['purp']);
        if( !empty( $payment['rmtInf'] ) )
            $drctDbtTxInf->addChild('RmtInf')->addChild('Ustrd', $payment['rmtInf']);
    }

}
