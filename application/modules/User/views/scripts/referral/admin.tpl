<? if(count($this->detail_list) !== 0): ?>
    <style>
        .referrals_link
        {
            font-size: 2em;
        }
        
        .referrals_details
        {
            border-collapse: collapse;
            font-size: 0.7em;
            margin: 20px auto;
        }
        
        .referrals_details th
        {
            background-color: #F5F7F8;
            height: 25px;
        }
        
        .referrals_details th,
        .referrals_details td
        {
            border: 1px solid #DEE4E8;
            font-size: 1.2em;
            padding: 2px 4px;
            text-align: center;
        }
    </style>
    
    <a class="referrals_link" href="/referrals/">← Назад к странице рефералов</a>
    <h1>Статистика по рефералам</h1>
    
    <table class="referrals_details">
        <thead>
            <tr>
                <th>№</th>
				<th>Id slave</th>
                <th>Приглаcитель</th>
                <th>Приглашённый</th>
                <th>Email</th>
				<th>День рождения</th>
                <th>IP</th>
                <th>Cookie</th>
                <th>Браузер</th>
                <th>utm_term</th>
                <th>Время</th>
            </tr>
        </thead>
        <tbody>
            <? for($i = 0; $i < count($this->detail_list); $i++): ?>
                <tr>
                    <td><?=($i+1);?></td>
                    <? foreach ($this->detail_list[$i] as $key => $value): ?>
                        <? if(!($key === 'idreferral' || $key === 'idmaster' || $key === 'idslave' || $key === 'location')): ?>
                            <? if($key === 'master_name'): ?>
                                <td><a href="http://<?=$_SERVER['SERVER_NAME'];?>/profile/<?=$this->detail_list[$i]['idmaster'];?>" target="_blank"><?=$value;?></a></td>
                            <? elseif($key === 'referral_name'): ?>
                                <td><a href="http://<?=$_SERVER['SERVER_NAME'];?>/profile/<?=$this->detail_list[$i]['idslave'];?>" target="_blank"><?=$value;?></a></td>
                            <? elseif($key === 'ip'): ?>
                                <td><?=$value;?><br/><?=$this->detail_list[$i]['location'];?></td>
                            <? elseif($key === 'user_agent'  && is_array($value)): ?>
                                <td>
                                    <img alt="<?=$value['ua_family'];?>" height="16" src="/application/libraries/UASparser/icons/user_agent/<?=$value['ua_icon'];?>" title="<?=$value['ua_family'];?>" width="16"/>
                                    <a href="<?=$value['ua_url'];?>" target="_blank" title="<?=$value['ua_company'];?>"><?=$value['ua_name'];?></a>
                                    <img alt="<?=$value['os_family'];?>" height="16" src="/application/libraries/UASparser/icons/operation_system/<?=$value['os_icon'];?>" title="<?=$value['os_family'];?>" width="16"/>
                                    <a href="<?=$value['os_url'];?>" target="_blank" title="<?=$value['os_company'];?>"><?=$value['os_name'];?></a>
                                </td>
                            <? else: ?>
                                <td><?=$value;?></td>
                            <? endif; ?>
                        <? endif; ?>
                    <? endforeach; ?>
                </tr>
            <? endfor; ?>
        </tbody>
    </table>
<? else: ?>
    <h3>Нет рефералов для отображения</h3>
<? endif; ?>
