<?php

include_once "/home/yiimp-data/yiimp/site/web/yaamp/AdminLTE/function.php";

$user = getuserparam(getparam('address'));
if (!$user) return;

$userid = intval($user->id);
$coinid = intval($user->coinid);
if ($coinid)
{
    $coin = getdbo('db_coins', $coinid);
}

openCard('card-primary','Miners: ('.$user->username.')');
echo '<div class="card-body table-responsive p-0">';

echo '<table class="table table-striped>';
echo "<thead>";
echo "<tr>";
echo "<th align=left>Summary</th>";
echo "<th align=right>Miners</th>";
echo "<th align=right>Shares</th>";
echo "<th align=right width=80>Hashrate*</th>";
echo "<th align=right width=60>Reject*</th>";
echo "</tr>";
echo "</thead>";

foreach (yaamp_get_algos() as $algo)
{
    if (!YAAMP_ALLOW_EXCHANGE && isset($coin) && $coin->algo != $algo) continue;

    $user_rate1 = yaamp_user_rate($userid, $algo);
    $user_rate1_bad = yaamp_user_rate_bad($userid, $algo);

    $percent_bad = ($user_rate1 + $user_rate1_bad) ? $user_rate1_bad * 100 / ($user_rate1 + $user_rate1_bad) : 0;
    $percent_bad = $percent_bad ? round($percent_bad, 1) . '%' : '';

    $user_rate1 = $user_rate1 ? Itoa2($user_rate1) . 'h/s' : '-';
    $minercount = getdbocount('db_workers', "userid=$userid AND algo=:algo", array(
        ':algo' => $algo
    ));

    if (YAAMP_ALLOW_EXCHANGE || !$user->coinid)
    {

        $user_shares = controller()
            ->memcache
            ->get_database_scalar("wallet_user_shares-$userid-$algo", "SELECT SUM(difficulty) FROM shares WHERE valid AND userid=$userid AND algo=:algo", array(
            ':algo' => $algo
        ));
        if (!$user_shares && !$minercount) continue;

        $total_shares = controller()
            ->memcache
            ->get_database_scalar("wallet_total_shares-$algo", "SELECT SUM(difficulty) FROM shares WHERE valid AND algo=:algo", array(
            ':algo' => $algo
        ));

    }
    else
    {
        // we know the single currency mined if auto exchange is disabled
        $user_shares = controller()
            ->memcache
            ->get_database_scalar("wallet_user_shares-$algo-$coinid-$userid", "SELECT SUM(difficulty) FROM shares WHERE valid AND userid=$userid AND coinid=$coinid AND algo=:algo", array(
            ':algo' => $algo
        ));
        if (!$user_shares) continue;

        $total_shares = controller()
            ->memcache
            ->get_database_scalar("wallet_coin_shares-$coinid", "SELECT SUM(difficulty) FROM shares WHERE valid AND coinid=$coinid AND algo=:algo", array(
            ':algo' => $algo
        ));
    }

    if (!$total_shares) continue;
    $percent_shares = round($user_shares * 100 / $total_shares, 4);

    echo '<tr class="ssrow">';
    echo '<td><b>' . $algo . '</b></td>';
    echo '<td align="right">' . $minercount . '</td>';

    $is_solo = getdbocount('db_workers',"algo=:algo and userid=:userid and password like '%m=solo%'", 
		    array(':algo'=>$algo,':userid'=>$userid));

    if($is_solo)
    {
	    echo '<td align="right" width="100"><span class="solo label">Solo</span></td>';
    }
    else
    {
	    echo '<td align="right" width="100">'.$percent_shares.'%</td>';
    }
    echo '<td align="right" width="100"><b>' . $user_rate1 . '</b></td>';
    echo '<td align="right">' . $percent_bad . '</td>';
    echo '</tr>';
}

echo "</table>";

////////////////////////////////////////////////////////////////////////////////
$workers = getdbolist('db_workers', "userid=$user->id order by password");
if (count($workers))
{
    echo "<br>";
    echo "<table  class='table table-sm'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Details</th>";
    if ($this->admin) echo "<th>IP</th>";
    echo "<th>Extra</th>";
    echo "<th>Algo</th>";
    echo "<th>Diff</th>";
    echo "<th title='extranonce.subscribe'>ES**</th>";
    echo "<th>Hashrate*</th>";
    echo "<th>Reject*</th>";
    echo "</tr>";
    echo "</thead>";

    foreach ($workers as $worker)
    {
        $user_rate1 = yaamp_worker_rate($worker->id, $worker->algo);
        $user_rate1_bad = yaamp_worker_rate_bad($worker->id, $worker->algo);
        $user_rejects = yaamp_worker_shares_bad($worker->id, $worker->algo);
        if (!$user_rejects) $user_rejects = '';

        $percent = ($user_rate1 + $user_rate1_bad) ? $user_rate1_bad * 100 / ($user_rate1 + $user_rate1_bad) : 0;
        $percent = $percent ? round($percent, 2) . '%' : '';

        $user_rate1 = $user_rate1 ? Itoa2($user_rate1) . 'h/s' : '';

        $version = substr($worker->version, 0, 20);
        $password = substr($worker->password, 0, 32);
        if (empty($password) && !empty($worker->worker)) $password = substr($worker->worker, 0, 32);

        $subscribe = Booltoa($worker->subscribe);

        echo '<tr class="ssrow">';
        echo '<td title="' . $worker->version . '">' . $version . '</td>';
        if ($this->admin) echo "<td>{$worker->ip}</td>";
        echo '<td title="' . $worker->password . '">' . $password . '</td>';
        echo '<td>' . $worker->algo . '</td>';
        echo '<td>' . $worker->difficulty . '</td>';
        echo '<td>' . $subscribe . '</td>';
        echo '<td>' . $user_rate1 . '</td>';
        echo '<td title="' . $percent . '">' . $user_rejects . '</td>';
        echo '</tr>';
    }

    echo "</table>";
}

echo "</div>";

echo "<br><p style='font-size: .8em'>
		&nbsp;* approximate from the last 5 minutes submitted shares<br>
		&nbsp;** extranonce.subscribe<br>
		</p>";
        
echo '</div>'; //card-body table-responsive p-0
echo "</div><br>";
