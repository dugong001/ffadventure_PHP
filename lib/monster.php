<?php
/**
 * Created by IntelliJ IDEA.
 * User: timomo
 * Date: 15/01/05
 * Time: 15:41
 */

function battle_monster( $in ) {
    $chara = load_chara_data( $_SESSION["id"] );
    $max_turn = read_config_option( "turn" );
    $chara_syoku = read_config_option( "chara_syoku" );
    $lv_up = read_config_option( "lv_up" );
    $script = read_config_option( "script" );
    
    if ( $chara["stamina"] == 0 ) {
        $stamina_time = read_config_option( 'stamina_time' );
        error_page( "スタミナが0の為、戦えません。<br />スタミナは". $stamina_time. "秒で1つ回復します。" );
    }
    
    $monster_file = read_config_option( 'monster_file' );
    $monsters = file( $monster_file );
    
    $m_no = count( $monsters ) - 1;
    $r_no = rand( 0, $m_no );
    
    $tmp = explode( "<>", $monsters[ $r_no ] );
    $mob["name"] = $tmp[0];
    $mob["ex"] = $tmp[1];
    $mob["hp"] = $tmp[2];
    $mob["sp"] = $tmp[3];
    $mob["dmg"] = $tmp[4];

    $khp = $chara["hp"];
    $khp_flg = $khp;
    $mhp = rand( 0, $mob["hp"] ) + $mob["sp"];
    $mhp_flg = $mhp;
    $win_flg = 0;
    
    show_header();
    
    ?>
    <h1><?php echo $chara["name"] ?>は<?php echo $mob["name"] ?>に戦いを挑んだ！</h1>
    <hr size="0" />
    <?php
    
    foreach ( range( 1, $max_turn ) as $turn ) {
        $dmg1 = $chara["lv"] * ( rand( 0, 5 ) + 1 );
        $dmg2 = ( rand( 0, $mob["dmg"] ) + 1 ) + $mob["dmg"];
        $clit1 = "";
        $clit2 = "";
        $com1 = "";
        $com2 = $mob["name"]. "が襲いかかった！！";
        $kawasi1 = "";
        $kawasi2 = "";
        
        $calc = get_player_attack_calculation( $chara );
        
        $com1 += $calc["com"];
        $dmg1 += $calc["dmg"];
        
        if ( rand( 0, 20 ) == 0 ) {
            $clit1 = <<<EOF
            <span>{$chara["name"]}「<b>{$chara["waza"]}</b>」</span><p><b class="clit">クリティカル！！</b>
EOF;
            $dmg1 = $dmg1 * 2;
        }
        
        if ( rand( 0, 30 ) == 0 ) {
            $clit2 = <<<EOF
            <b class="clit">クリティカル！！</b>
EOF;
            $dmg2 = $dmg2 * 1.5;
        }
        ?>
        <table border="0">
            <tr>
                <td class="b2" colspan="3" align="center">
                    <?php $turn ?>ターン
                </td>
            </tr>
            <tr>
                <td>
                    <table border="1">
                        <tr>
                            <td class="b1">
                                名前
                            </td>
                            <td class="b1">
                                HP
                            </td>
                            <td class="b1">
                                職業
                            </td>
                            <td class="b1">
                                LV
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $chara["name"] ?>
                            </td>
                            <td>
                                <?php echo $khp_flg ?>/<?php echo $chara["maxhp"] ?>
                            </td>
                            <td>
                                <?php echo $chara_syoku[ $chara["syoku"] ] ?>
                            </td>
                            <td>
                                <?php echo $chara["lv"] ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <span style="font-size: 10pt; color: #9999DD;">VS</span>
                </td>
                <td>
                    <table border="1">
                        <tr>
                            <td class="b1">
                                名前
                            </td>
                            <td class="b1">
                                HP
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $mob["name"] ?>
                            </td>
                            <td>
                                <?php echo $mhp_flg ?>/<?php echo $mhp ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div>
            <?php echo $com1 ?> <?php echo $clit1 ?> <?php echo $kawasi2 ?> <?php echo $mob["name"] ?>に <span class="dmg"><?php echo $dmg1 ?></span> のダメージを与えた。<br />
            <?php echo $com2 ?> <?php echo $clit2 ?> <?php echo $kawasi1 ?> <?php echo $chara["name"] ?>に <span class="dmg"><?php echo $dmg2 ?></span> のダメージを与えた。<br />
        </div>
        <?php
        
        $khp_flg -= $dmg2;
        $mhp_flg -= $dmg1;
        
        if ( $mhp_flg <= 0 ) {
            $win_flg = 1;
            break;
        } elseif ( $khp_flg <= 0 ) {
            $win_flg = 0;
            break;
        }
    }
    
    if ( $win_flg == 1 ) {
        $chara["total"] += 1;
        $chara["kati"] += 1;
        $mex = $mob["ex"];
        $chara["ex"] += $mex;
        use_stamina( $chara, 1 );
        $gold = $chara["lv"] * 10 + rand( 0, $chara["lp"] );
        $chara["gold"] += $gold;
        ?>
        <div>
            <span style="font-size: 10pt;"><?php echo $chara["name"] ?>は戦闘に勝利した！！</span>
            <?php echo $mex ?>の経験値を手に入れた。<br />
            <?php echo $gold ?>Gを手に入れた。
        </div>
        <?php
    } else {
        $chara["total"] += 1;
        $mex = rand( 0, $chara["lp"] );
        $chara["ex"] += $mex;
        use_stamina( $chara, 1 );
        $gold = 0;
        $chara["gold"] += $gold;
        ?>
        <div>
            <span style="font-size: 10pt;"><?php echo $chara["name"] ?>は戦闘に負けた・・・。</span>
            <?php echo $mex ?>の経験値を手に入れた。<br />
            <?php echo $gold ?>Gを手に入れた。
        </div>
        <?php
    }
    
    if ( $chara["ex"] > ( $chara["lv"] * $lv_up ) ) {
        $maxhp_flg = rand( 0, $chara["n_3"] ) + 1;
        $chara["maxhp"] += $maxhp_flg;
        $chara["hp"] = $chara["maxhp"];
        $chara["ex"] = 0;
        $chara["lv"] += 1;
        $t = array( "", "", "", "", "", "", "" );
        
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_0"] += 1;
            $t[0] = "力";
        }
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_1"] += 1;
            $t[1] = "知力";
        }
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_2"] += 1;
            $t[2] = "信仰心";
        }
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_3"] += 1;
            $t[3] = "生命力";
        }
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_4"] += 1;
            $t[3] = "器用さ";
        }
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_5"] += 1;
            $t[3] = "速さ";
        }
        if ( rand( 0, 5 ) == 0 ) {
            $chara["n_6"] += 1;
            $t[3] = "魅力";
        }

        foreach ( range( 0, 6 ) as $i ) {
            if ( $t[$i] == "" ) {
                continue;
            }
            ?>
            <?php echo $t[$i] ?>が上がった。<br />
            <?php
        }
    }
    
    $chara["hp"] = $khp_flg + rand( 0, $chara["n_3"] );
    if ( $chara["hp"] > $chara["maxhp"] ) {
        $chara["hp"] = $chara["maxhp"];
    }
    if ( $chara["hp"] <= 0 ) {
        $chara["hp"] = 0;
    }

    save_chara_data( $chara );

    ?>
    <form action="<?php echo $script ?>" method="post">
        <input type="hidden" name="mode" value="log_in" />
        <input type="hidden" name="id" value="<?php echo $chara["id"] ?>" />
        <input type="hidden" name="pass" value="<?php echo $chara["pass"] ?>" />
        <input type="submit" value="ステータス画面へ" />
    </form>
    <?php
    
    show_footer();
}
