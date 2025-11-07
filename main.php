<?php
// 基本的な変数
// $board -> 盤面を表す8×8の配列。値が-1だと白石、1だと黒石が置かれていて、0だと何も置かれていない
// $stone -> 手番を表す変数で、-1、0、1の3つの値を取る。値が-1だと白番、1だと黒番、0だとゲーム終了を表す

// 初期化またはPOSTデータの復元
// hiddenから盤面を受け取る
if (isset($_POST['board']) && isset($_POST['stone']) && is_array($_POST['board'])) {
    $board = $_POST['board'];
    $stone = (int)$_POST['stone'];
} else {
    list($board, $stone) = init();
}

// 石を置いたときの処理
$pass = false; // パスの時のみtrueになる。
if (isset($_POST['move'])) {
    // 受け取った「$r,$c」の値を$rと$cに分割
    $parts = explode(',', $_POST['move']);
    if (count($parts) == 2) {
        $r = (int)$parts[0];
        $c = (int)$parts[1];

        if ($board[$r][$c] == 0 && judgeStone($board, $r, $c, $stone)) { // まだ置いてないかつ打てる場所であるとき
            flipStone($board, $r, $c, $stone); // 石を返す

            if (!canPlay($board, -$stone)) { // 次のプレイヤーが置けないならパス
                if (!canPlay($board, $stone)) { // 両方打てないとき
                    $stone = 0; // ゲーム終了
                } else {
                    $pass = true; // パス
                }
            } else { // 次のプレイヤーが置けるとき
                $stone = -$stone; // 手番を次のプレイヤーに更新
            }
        }
    }
}

// リセットボタン
if (isset($_POST['reset'])) {
    list($board, $stone) = init();
}

// ===========================================
// 関数
// ===========================================
// 初期盤面の生成
function init() {
    $board = array_fill(0, 8, array_fill(0, 8, 0));
    $board[3][3] = -1; // 白
    $board[3][4] = 1;  // 黒
    $board[4][3] = 1;  // 黒
    $board[4][4] = -1; // 白
    $stone = 1; // 黒番から

    return array($board, $stone);
}

// 置ける場所の判定
function judgeStone($bd, $r, $c, $stone) {
    // 既に石が置かれている場所には置けない
    if ($bd[$r][$c] != 0) {
        return false;
    }

    // 上下左右斜め方向に対し、打てるかどうかを調べる
    for ($di = -1; $di <= 1; $di++) { // 横方向
        for ($dj = -1; $dj <= 1; $dj++) { // 縦方向
            // 自分自身はスキップ
            if ($di == 0 && $dj == 0) {
                continue;
            }

            // 調べるマスの初期値
            $i = $r + $di;
            $j = $c + $dj;

            // 盤外ならスキップ
            if($i < 0 || $i > 7 || $j < 0 || $j > 7) { 
                continue; 
            }

            while($bd[$i][$j] == -$stone) { // 調べるマスに相手の石がある間
                // 調べるマスを(di, dj)ベクトルの方向に移動
                $i += $di;
                $j += $dj;
                // 盤外に行くなら置けない
                if($i < 0 || $i > 7 || $j < 0 || $j > 7) { 
                    break; 
                }
                // 移動した先に石がなければ置けない
                if($bd[$i][$j] == 0) { 
                    break; 
                }
                // 移動した先に自分の石があるとき置ける
                if($bd[$i][$j] == $stone) {
                    return true; // 置ける
                }
            }
        }
    }
    return false; // 置けない
}

// 石を返す 
function flipStone(&$bd, $r, $c, $stone) { 
    $bd[$r][$c] = $stone; 
    //上下左右斜め方向に対し、打てるかどうかを調べる 
    for($di = -1; $di <= 1; $di++) { // 横方向
        for($dj = -1; $dj <= 1; $dj++) { // 縦方向 
        // 自分自身はスキップ 
        if($di == 0 && $dj == 0) { 
            continue; 
        } 
        // 調べるマスの初期値 
        $i = $r + $di; 
        $j = $c + $dj; 
        // 盤外ならスキップ 
        if($i < 0 || $i > 7 || $j < 0 || $j > 7) { 
            continue; 
        }

        while($bd[$i][$j] == -$stone) { // 調べるマスに相手の石がある間
            // 調べるマスを(di, dj)ベクトルの方向に移動
            $i += $di; 
            $j += $dj;
            // 盤外に行くなら置けない
            if($i < 0 || $i > 7 || $j < 0 || $j > 7) { 
                break; 
            } 
            // 移動した先に石がなければ置けない
            if($bd[$i][$j] == 0) { 
                break; 
            }
            // 移動した先に自分の石があるとき置ける
            if($bd[$i][$j] == $stone) { 
                // 移動してきた方向の逆向きに元いた位置まで戻って石を返す
                $i -= $di; 
                $j -= $dj; 
                while(!($i == $r && $j == $c)) {
                    $bd[$i][$j] = $stone;
                    $i -= $di; 
                    $j -= $dj; 
                } 
            } 
        } 
        } 
    } 
}

// 打てる場所が残っているかの判定
function canPlay($bd, $stone) {
    for ($r = 0; $r < 8; $r++) {
        for ($c = 0; $c < 8; $c++) {
            if (judgeStone($bd, $r, $c, $stone)) {
                return true; // 打てる
            }
        }
    }
    return false; // 打てない
}

// 石をカウント
function countStone($bd, $stone) {
    $count = 0; // カウンターの初期化
    for($r = 0; $r < 8; $r++) {
        for($c = 0; $c < 8; $c++) {
            if($bd[$r][$c] == $stone) {
                $count++;
            }
        }
    }
    return $count;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<title>シンプルオセロ</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<?php
	$black = countStone($board, 1); // 黒石の数
	$white = countStone($board, -1); // 白石の数
	?>

	<!-- メッセージ表示部分 -->
	<?php if ($stone == 0): ?> <!-- ゲーム終了時の表示 --> 
		<h2 class="message">ゲーム終了！<?= $black > $white ? '黒の勝ち！' : ($white > $black ? '白の勝ち！' : '引き分け！') ?></h2>
	<?php elseif(isset($pass) && $pass): ?> <!-- パス発生時の表示 -->
		<h2 class="message">パスです。もう一度<?= $stone == 1 ? '黒' : '白' ?>の番です。</h2>
	<?php else: ?> <!-- 通常時の表示 -->
		<h2 class="message"><?= $stone == 1 ? '黒' : '白' ?>の番です</h2>
	<?php endif; ?>
	<!-- メッセージ表示部分ここまで -->

	<!-- 枚数表示 -->
	<div class="message">
		<div>黒：<?= $black ?>枚　白：<?= $white ?>枚</div>
	</div>
	<!-- 枚数表示ここまで -->

	<form method="post">
		<!-- $boardのフォーム送信部分 -->
		<?php for ($r = 0; $r < 8; $r++): ?>
			<?php for ($c = 0; $c < 8; $c++): ?>
			<input type="hidden" name="board[<?= $r ?>][<?= $c ?>]" value="<?= $board[$r][$c] ?>">
			<?php endfor; ?>
		<?php endfor; ?>
		<!-- $boardのフォーム送信部分ここまで -->
		<input type="hidden" name="stone" value="<?= $stone ?>"> <!-- $stoneのフォーム送信 -->

		<!-- 盤面描画部分 -->
		<table>
		<?php for ($r = 0; $r < 8; $r++): ?>
			<tr>
			<?php for ($c = 0; $c < 8; $c++): ?>
				<td>
					<!-- moveという名前で押された場所をフォーム送信。ゲーム終了時にはbuttonをdisabledにする -->
					<button type="submit" name="move" value="<?= $r . ',' . $c ?>" <?= $stone == 0 ? 'disabled' : '' ?>>
						<?php if ($board[$r][$c] == 1): ?> <!-- $boardから黒石を描画 -->
							<div class="black"></div>
						<?php elseif ($board[$r][$c] == -1): ?> <!-- $boardから白石を描画 -->
							<div class="white"></div>
						<?php elseif ($stone != 0 && judgeStone($board, $r, $c, $stone)): ?> <!-- 置ける場所を灰色の丸で描画 -->
							<div class="place"></div>
						<?php endif; ?>
					</button>
				</td>
			<?php endfor; ?>
			</tr>
		<?php endfor; ?>
		</table>
		<!-- 盤面描画部分ここまで -->
	</form>

	<form method="post" class="btn">
		<input type="submit" name="reset" value="リセット"> <!-- リセットボタン -->
	</form>
</body>
</html>
