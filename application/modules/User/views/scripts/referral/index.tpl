<style>
    .stats_link
    {
        font-size: 2em;
    }
    
    h3
    {
        background-color: #F5F7F8;
        border: 1px solid #DEE4E8;
    }
</style>
<script type="text/javascript" src="http://vk.com/js/api/share.js" charset="windows-1251"></script>
<h1><?=$this->translate('The number of people invited by users'); ?></h1>
<br/>
<div>
    <div style="float: left; margin-right: 10px;"><?=$this->translate('To invite your friend use this link: '); ?><input readonly="readonly" size="<?=strlen($this->link);?>" type="text" value="<?=$this->link?>"/></div>
    <!--div style="float: left;"><script type="text/javascript">document.write(VK.Share.button({url: "<?=$this->link?>",
                description: "Уважаемые школьники 7-11 классов! Приглашаем вас всех к участию в первом туре олимпиады «Физтех» 2015 года. Участие в нашей олимпиаде позволяет получить льготы  при поступлении в МФТИ и другие топовые вузы России: победители и призёры очного (второго) этапа олимпиады по физике и/или по математике смогут зачесть 100 баллов ЕГЭ по соответствующей их диплому дисциплине.",
                title: "<?=bitsa_SITE;?> — онлайн-этап олимпиады «Физтех»",
                image: "http://<?=bitsa_SITE;?>/public/admin/olymp_logo.png",
                noparse: false}, {type: "round_nocount", text: "Рассказать друзьям"}));</script></div-->
</div>
<br/>    
<br/>
<ol class="reftable">
    <?
        $maxref = 0;
        foreach ($this->list as $user) 
        {
            if ($user['referrals_count'] > $maxref && ($user['idmaster'] != SMM_USER_ID || $this->user_id === SMM_USER_ID || $this->user_id === 11))
            {
                $maxref = $user['referrals_count'];
            }
        }
        
        $i = 1;
        
        foreach ($this->list as $user) 
        {
            if ($user['idmaster'] != SMM_USER_ID || $this->user_id === SMM_USER_ID || $this->user_id === 11)
            {
                $lineclass = 'line';
                if ($i%2 == 0)
                {
                    $lineclass .= ' even';
                }

                if ($user['idmaster'] == $this->user_id)
                {
                    $lineclass .= ' mybar';
                }

                $width_div = 650 * $user['referrals_count']/$maxref;            
            
                echo '<li class="' . $lineclass . '"><div class="place">' . $i . '.</div><a class="user" href="http://' . bitsa_SITE . '/profile/' . $user['idmaster'] . '" target="_blank">' . $user['master_name'] . '</a><div class="bar ref_bar" style="width:' . $width_div . 'px">' . $user['referrals_count'] . '</div></li>';
                $i++;
            }
        }
    ?>
</ol>
<br/>
<? if($this->user_id == SMM_USER_ID): ?>
    <style>
        .referrals_details.referrals_stats
        {
            font-size: 1em;
            padding: 5px;
            width: 100%;
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
    
    <br/>
    <br/>
    <br/>
    <h3><?=$this->translate('Referrals summary statistics'); ?></h3>
    <? $result_count = 0; ?>
    <table class="referrals_details referrals_stats">
        <thead>
            <tr>
                <th>utm_term</th>
                <th>Число людей</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            <? for($i = 0; $i < count($this->stats_list); $i++): ?>
                <? $result_count += $this->stats_list[$i]['count']; ?>    
            <? endfor; ?>
            
            <? for($i = 0; $i < count($this->stats_list); $i++): ?>
                <tr>
                    <? if($this->stats_list[$i]['utm_term'] == null): ?>
                        <th>Без реферала</th>
                    <? else: ?>
                        <th><?=$this->stats_list[$i]['utm_term'];?></th>
                    <? endif; ?>
                        
                    <td><?=$this->stats_list[$i]['count'];?></td>
                    <td><?=number_format($this->stats_list[$i]['count']*100/$result_count, 2);?></td>
                </tr>
            <? endfor; ?>
            <tr>
                <th>Всего по рефералам</th>
                <td><?=($result_count - $this->stats_list[0]['count']);?></td>
                <td><?=number_format(($result_count - $this->stats_list[0]['count']) * 100/$result_count, 2);?></td>
            </tr>    
            <tr>
                <th>Всего</th>
                <td><?=$result_count;?></td>
                <td>100%</td>
            </tr>
        </tbody>
    </table>
    
    <br/>
    <br/>
    <br/>
    <h3>Ваша статистика по рефералам</h3>
    
    <table class="referrals_details">
        <thead>
            <tr>
                <th>№</th>
                <th>Приглашённый</th>
                <th>Место</th>
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
                        <? if(!($key === 'idreferral' || $key === 'idslave')): ?>
                            <? if($key === 'referral_name'): ?>
                                <td><a href="http://<?=bitsa_SITE;?>/profile/<?=$this->detail_list[$i]['idslave'];?>" target="_blank"><?=$value;?></a></td>
                            <? elseif($key === 'location'): ?>
                                <td><?=$value;?></td>
                            <? elseif($key === 'user_agent' && is_array($value)): ?>
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
<? endif; ?>

<br/>
<? if($this->level_id == 1 || $this->level_id == 2): ?>
    <a class="stats_link" href="/referrals/admin/" target="_blank"><?=$this->translate('Main statistics on referrals'); ?></a>
<? endif; ?>
