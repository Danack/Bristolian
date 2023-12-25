<?php

namespace Bristolian\Repo\WebPushSubscriptionRepo;

use Bristolian\DataType\WebPushSubscriptionParam;
//use Bristolian\Model\User;
use Bristolian\PdoSimple;
use Bristolian\Model\UserWebPushSubscription;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;

class PdoWebPushSubscriptionRepo implements WebPushSubscriptionRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function getUserSubscriptions(string $user_id): array
    {

        $sql = <<< SQL
select
  endpoint,
  expiration_time,
  raw  
from 
  user_webpush_subscription
where
  user_id = :user_id
SQL;

//        \Bristolian\PdoSimple::fetchAllAsData
//        fetchAllAsObject

        $userWebPushSubscriptionsData = $this->pdo_simple->fetchAllAsData(
            $sql,
            ['user_id' => $user_id],
            //            UserWebPushSubscription::class
        );


        $subscriptions = [];

        foreach ($userWebPushSubscriptionsData as $userWebPushSubscriptionsDatum) {
            $subscriptions[] = new UserWebPushSubscription(
                $userWebPushSubscriptionsDatum["endpoint"],
                $userWebPushSubscriptionsDatum["expiration_time"],
                $userWebPushSubscriptionsDatum["raw"]
            );
        }


        return $subscriptions;
    }


    /**
     * @throws UserConstraintFailedException
     */
    public function save(
        string $user_id,
        WebPushSubscriptionParam $webPushSubscriptionParam,
        string $raw
    ): void {

        $sql = <<< SQL
insert into user_webpush_subscription (
  user_id,
  endpoint,
  expiration_time,
  raw
)
values (
  :user_id,
  :endpoint,
  :expiration_time,
  :raw
)
SQL;
        $params = [
          'user_id' => $user_id,
          'endpoint' => $webPushSubscriptionParam->endpoint,
          'expiration_time' => $webPushSubscriptionParam->expiration_time,
          'raw' => $raw,
        ];

        try {
            $this->pdo_simple->insert($sql, $params);
        }
        catch (\PDOException $pdoException) {
            // TODO - technically, this should check the message also.
            if ((int)$pdoException->getCode() === 23000) {
                throw new UserConstraintFailedException(
                    "Failed to insert, user constraint errored.",
                    $pdoException->getCode(),
                    $pdoException
                );
            }

            // Rethrow original exception as it wasn't a failure to insert.
            throw $pdoException;
        }
    }
}
