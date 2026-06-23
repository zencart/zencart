<?php // Spanish Language Pack for Zen Cart: https://github.com/torvista/Zen_Cart-Spanish_Language_Pack
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2023 Jan 16 New in v1.5.8a $
 */


$define = [
    'HEADING_TITLE' => 'Mostrar los Debug-logs',
    'TABLE_HEADING_FILENAME' => 'Nombre',
    'TABLE_HEADING_MODIFIED' => 'Fecha',
    'TABLE_HEADING_FILESIZE' => 'Tamaño (b)',
    'TABLE_HEADING_DELETE' => '¿Borrar?',
    'TABLE_HEADING_ACTION' => 'Acción',
    'BUTTON_INVERT_SELECTED' => 'Invertir Seleccción',
    'BUTTON_DELETE_SELECTED' => 'Borrar Seleccionado(s)',
    'DELETE_SELECTED_ALT' => 'Borrar Todos los archivos seleccionados',
    'BUTTON_DELETE_ALL' => 'Borrar Todos',
    'DELETE_ALL_ALT' => 'Borrar todos los archivos mostrados',
    'ICON_INFO_VIEW' => 'Ver el contenido de este archivo',
    'DISPLAY_DEBUG_LOGS_ONLY' => '¿Mostrar solamente los debugs?',
    'TEXT_HEADING_INFO' => 'Contenido',
    'TEXT_MOST_RECENT' => 'más reciente',
    'TEXT_OLDEST' => 'más antiguo',
    'TEXT_SMALLEST' => 'menor',
    'TEXT_LARGEST' => 'mayor',
    'TEXT_INSTRUCTIONS' => '<p>Los archivos se pueden ordenar en orden ascendente o descendente pinchando en los enlaces de las columnas <em>Asc</em> o <em>Desc</em>.</p> <p>Haga clic en el icono %7$s para ver el contenido del archivo asociado. Solo se leerán/mostrarán los primeros %1$u bytes del archivo seleccionado; si un archivo tiene &quot;un tamaño grande&quot;, su <em>Tamaño </em> se resaltará como <span class="bigfile">este</span>.</p><ul><li>< strong>Borrar Todos</strong> eliminará todos los archivos que se muestran actualmente.</li><li><strong>Borrar Seleccionados</strong> eliminará solo aquellos archivos con las casillas marcadas.</li><li><strong >Invertir Selección</strong> intercambiará casillas marcadas por no marcadas y viceversa. Por ejemplo, si desea eliminar todos los archivos excepto uno, marque la casilla del archivo que desea conservar, luego "Invertir Selección" y finalmente "Borrar Seleccionados".</li></ul><p>Actualmente mostrando %2$s %3$u de %4$u archivos que tienen estos prefijos:<br><code>%5$s</code><br>y<b>no</b> coinciden con los prefijos opcionales/definidos por el usuario: <code>%6$s</code>.</p>',
    'JS_MESSAGE_DELETE_ALL_CONFIRM' => '¿Está seguro que quiere borrar estos \'+n+\' archivos?',
    'JS_MESSAGE_DELETE_SELECTED_CONFIRM' => '¿Está seguro que quiere borrar los \'+selected+\' archivo(s) seleccionados?',
    'WARNING_NOT_SECURE' => '<span class="errorText">NOTA: SSL no habilitado. El contenido del archivo que visualiza en esta página no será encriptado y puede ser un riesgo de seguridad.</span>',
    'WARNING_NO_FILES_SELECTED' => '¡No había archivos seleccionados para borrar!',
    'WARNING_SOME_FILES_DELETED' => 'Aviso: Solamente se borraron %u de %u archivos; revise los permisos de los archivos/directorio.',
    'SUCCESS_FILES_DELETED' => 'Se borró %u archivos.',
];

return $define;
