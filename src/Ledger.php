<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 2017-06-24
 * Time: 1:33 PM.
 */

namespace DanDoingDev\Ledger;

use DanDoingDev\Ledger\Exceptions\InsufficientBalanceException;
use DanDoingDev\Ledger\Exceptions\InvalidRecipientException;
use Illuminate\Routing\Router;

class Ledger
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * Ledger constructor.
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * credit a ledgerable instance.
     *
     * @param string $from
     * @param mixed  $currency
     * @param mixed  $to
     * @param mixed  $amount
     * @param mixed  $reason
     *
     * @return mixed
     */
    public function credit($to, $from, $amount, $currency, $reason)
    {
        $balance = $to->balance();
        $balance_currency = isset($to->balance_currency) ? $to->balance_currency : null;

        $data = [
            'money_from' => $from,
            'credit' => 1,
            'reason' => $reason,
            'amount' => $amount,
            'currency' => $currency,
            'balance' => (float) $balance + (float) $amount,
            'balance_currency' => $balance_currency,
        ];

        return $this->log($to, $data);
    }

    /**
     * debit a ledgerable instance.
     *
     * @param mixed $from
     * @param mixed $to
     *
     * @return mixed
     *
     * @throws InsufficientBalanceException
     */
    public function debit($from, $to, float $amount, string $currency, string $reason = null)
    {
        $balance = $from->balance();
        $balance_currency = isset($from->balance_currency) ? $from->balance_currency : null;

        if (0 == (float) $balance || (float) $amount > (float) $balance) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $data = [
            'money_to' => $to,
            'reason' => $reason,
            'amount' => $amount,
            'currency' => $currency,
            'balance' => (float) $balance - (float) $amount,
            'balance_currency' => $balance_currency,
        ];

        return $this->log($from, $data);
    }

    /**
     * topup a ledgerable instance.
     *
     * @param string     $to
     * @param mixed      $currency
     * @param mixed      $amount
     * @param null|mixed $reason
     * @param mixed      $from
     *
     * @return mixed
     *
     * @throws InsufficientBalanceException
     */
    public function topUp($to, $amount, $currency, $reason = null)
    {
        $balance = $to->balance();
        $balance_currency = isset($to->balance_currency) ? $to->balance_currency : null;

        $data = [
            'money_to' => $to,
            'credit' => 1,
            'reason' => $reason,
            'amount' => $amount,
            'currency' => $currency,
            'balance' => (float) $balance + (float) $amount,
            'balance_currency' => $balance_currency,
        ];

        return $this->log($to, $data);
    }

    /**
     * balance of a ledgerable instance.
     *
     * @param mixed $ledgerable
     *
     * @return float
     */
    public function balance($ledgerable)
    {
        $credits = $ledgerable->credits()->sum('amount');
        $debits = $ledgerable->debits()->sum('amount');

        return $credits - $debits;
    }

    /**
     * transfer an amount to each ledgerable instance.
     *
     * @param string $reason
     * @param mixed  $currency
     * @param mixed  $from
     * @param mixed  $to
     * @param mixed  $amount
     *
     * @return mixed
     *
     * @throws InvalidRecipientException
     * @throws InsufficientBalanceException
     */
    public function transfer($from, $to, $amount, $currency, $reason = 'funds transfer')
    {
        if (!is_array($to)) {
            return $this->transferOnce($from, $to, $amount, $reason);
        }

        $total_amount = (float) $amount * count($to);
        if ($total_amount > $from->balance()) {
            throw new InsufficientBalanceException('Insufficient balance');
        }

        $recipients = [];
        foreach ($to as $recipient) {
            array_push($recipients, $this->transferOnce($from, $recipient, $amount, $currency, $reason));
        }

        return $recipients;
    }

    /**
     * register routes for ledger api access.
     */
    public function routes()
    {
        $this->router->group(['namespace' => 'DanDoingDev\Ledger\Http\Controllers', 'prefix' => 'entries'], function () {
            $this->router->get('ledger', 'LedgerController@index');
            $this->router->get('ledger/{entry_id}', 'LedgerController@show');
        });
    }

    /**
     * persist an entry to the ledger.
     *
     * @param mixed $ledgerable
     *
     * @return mixed
     */
    protected function log($ledgerable, array $data)
    {
        return $ledgerable->entries()->create($data);
    }

    /**
     * transfer an amount to one ledgerable instance.
     *
     * @param mixed      $currency
     * @param mixed      $from
     * @param mixed      $to
     * @param mixed      $amount
     * @param null|mixed $reason
     *
     * @return mixed
     *
     * @throws InsufficientBalanceException
     * @throws InvalidRecipientException
     */
    protected function transferOnce($from, $to, $amount, $currency = 'USD', $reason = null)
    {
        if (get_class($from) == get_class($to) && $from->id == $to->id) {
            throw new InvalidRecipientException('Source and recipient cannot be the same object');
        }

        $this->credit($from, $to->name, $amount, $currency, $reason);

        return $this->debit($to, $from->name, $amount, $currency, $reason);
    }
}
