<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: extra_alpha_filter_search.php 2989 2012-10-17 04:07:25Z ajeh $
 */

// A B C D E F G H I J K L M N O P Q R S T U V W X Y Z 0 1 2 3 4 5 6 7 8 9
//define('PRODUCT_LIST_ALPHA_SORTER_LIST', 'A:A;B:B;C:C;D:D;E:E;F:F;G:G;H:H;I:I;J:J;K:K;L:L;M:M;N:N;O:O;P:P;Q:Q;R:R;S:S;T:T;U:U;V:V;W:W;X:X;Y:Y;Z:Z;0:0;1:1;2:2;3:3;4:4;5:5;6:6;7:7;8:8;9:9');

// A - C D - F G - I J - L M - N O - Q R - T U - W X - Z #
define('PRODUCT_LIST_ALPHA_SORTER_LIST', 'A - C:A,B,C;D - F:D,E,F;G - I:G,H,I;J - L:J,K,L;M - N:M,N;O - Q:O,P,Q;R - T:R,S,T;U - W:U,V,W;X - Z:X,Y,Z;#:0,1,2,3,4,5,6,7,8,9');
