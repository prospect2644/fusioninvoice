<?php

ob_start();
phpinfo(INFO_MODULES);
$info = ob_get_contents();
ob_end_clean();
$info = stristr($info, 'Client API version');
preg_match('/[1-9].[0-9].[1-9][0-9]/', $info, $match);
$mySqlVersion = $match[0];

$requirements = [
    [
        'requirement' => 'PHP Version',
        'required'    => '7.2.5',
        'actual'      => PHP_VERSION,
        'result'      => ((version_compare(PHP_VERSION, '7.2.5') >= 0) ? 1 : 0),
    ],
    [
        'requirement' => 'Fileinfo Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('fileinfo')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('fileinfo')) ? 1 : 0),
    ],
    [
        'requirement' => 'OpenSSL Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('openssl')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('openssl')) ? 1 : 0),
    ],
    [
        'requirement' => 'PDO Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('pdo')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('pdo')) ? 1 : 0),
    ],
    [
        'requirement' => 'PDO MySQL Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('pdo_mysql')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('pdo_mysql')) ? 1 : 0),
    ],
    [
        'requirement' => 'MBString Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('mbstring')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('mbstring')) ? 1 : 0),
    ],
    [
        'requirement' => 'Tokenizer Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('tokenizer')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('tokenizer')) ? 1 : 0),
    ],
    [
        'requirement' => 'Graphics Drawing Extension',
        'required'    => 'Yes',
        'actual'      => ((extension_loaded('gd')) ? 'Yes' : 'No'),
        'result'      => ((extension_loaded('gd')) ? 1 : 0),
    ],
    [
        'requirement' => 'Webserver',
        'required'    => 'Yes',
        'actual'      => $_SERVER["SERVER_SOFTWARE"],
        'result'      => 1,
    ],
    [
        'requirement' => 'Database Version',
        'required'    => 'Yes',
        'actual'      => 'MySql : '.$mySqlVersion,
        'result'      => 1,
    ],
];

function logReader($filename, $lines = 600, $buffer = 4096)
{
    // Open the file
    $f = fopen($filename, "rb");

    // Jump to last character
    fseek($f, -1, SEEK_END);

    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if (fread($f, 1) != "\n") $lines -= 1;

    // Start reading
    $output = '';
    $chunk  = '';

    // While we would like more
    while (ftell($f) > 0 && $lines >= 0)
    {
        // Figure out how far back we should jump
        $seek = min(ftell($f), $buffer);

        // Do the jump (backwards, relative to where we are)
        fseek($f, -$seek, SEEK_CUR);

        // Read a chunk and prepend it to our output
        $output = ($chunk = fread($f, $seek)) . $output;

        // Jump back to where we started reading
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

        // Decrease our line counter
        $lines -= substr_count($chunk, "\n");
    }

    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while ($lines++ < 0)
    {
        // Find first newline and remove all text before that
        $output = substr($output, strpos($output, "\n") + 1);
    }

    // Close file and return
    fclose($f);
    return $output;
}

?>

<table border="1">
    <tr>
        <th>Requirement</th>
        <th>Required</th>
        <th>Actual</th>
        <th>Result</th>
    </tr>
    <?php foreach ($requirements as $requirement)
    { ?>
        <tr>
            <td><?php echo $requirement['requirement']; ?></td>
            <td><?php echo $requirement['required']; ?></td>
            <td><?php echo $requirement['actual']; ?></td>
            <td><?php if ($requirement['result'] == 1)
                { ?><span style="color: green;">Pass</span><?php }
                else
                { ?><span style="color: red;">Fail</span><?php } ?></td>
        </tr>
    <?php } ?>
</table>
<br>
<table border="1">
    <tr>
        <td><strong><center>Recent Log:</center></strong></td>
    </tr>
    <tr>
        <td>
            <textarea rows="18" cols="110" readonly="readonly" disabled><?php echo logReader('storage/logs/laravel.log') ?></textarea>
        </td>
    </tr>
</table>
