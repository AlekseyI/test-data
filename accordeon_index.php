<?php
include "RenderTemplate.php";

function getCountInBranchTree($data, &$count)
{
//    if ($count == 0)
//    {
//        $count += $data['count'];
//        if (!isset($data['childs']))
//        {
//            return;
//        }
//        $data = $data['childs'];
//    }

    foreach ($data as $value)
    {
        $count += $value['count'];
        if (isset($value['childs']))
        {
            getCountInBranchTree($value['childs'], $count);
        }
    }
}

function generateTree($data, $parentId = 0) : array
{
    $branch = [];
    foreach ($data as $value) {
        if ($value['id_parent'] == $parentId) {
            $childs = generateTree($data, $value['id']);
            if ($childs) {
                $value['childs'] = $childs;
            }
            $branch[] = $value;
        }
    }
    return $branch;
}

function recalculateCountBranchesTree(&$data)
{
    foreach ($data as &$value)
    {
        if (isset($value['childs']))
        {
            $count = 0;
            getCountInBranchTree($value['childs'], $count);
            $value['count'] += $count;
            recalculateCountBranchesTree($value['childs']);
        }
    }
}

function generateMenu(array $data) : string
{
    $result = '';
    foreach ($data as $value)
    {
        $result .= getMenuHtml($value);
    }
    return $result;
}

function getMenuHtml($value) : string
{
    ob_start();
    include __DIR__ . '/menu.php';
    return ob_get_clean();
}

function getBranchTreeById($data, $id, &$result)
{
    foreach ($data as $value)
    {
        if ($value['id'] == $id)
        {
            $result = $value;
            break;
        }
        else
        {
            if (isset($value['childs']))
            {
                getBranchTreeById($value['childs'], $id, $result);
            }
        }
    }
}

function getIdsFromBranchTree($data, &$id)
{
    if (!$id)
    {
        $id[] = $data['id'];
        if (!isset($data['childs']))
        {
            return;
        }
        $data = $data['childs'];
    }

    foreach ($data as $value)
    {
        $id[] = $value['id'];
        if (isset($value['childs']))
        {
            getIdsFromBranchTree($value['childs'], $id);
        }
    }
}

$con = new PDO("mysql:host=localhost;dbname=test_base",
    "root",
    "root",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try
{
    $groups = $con->query("select `groups`.id, `groups`.id_parent, `groups`.name, count(*) as `count` from `groups` join `products` on `groups`.id = `products`.id_group group by `groups`.id")->fetchAll(PDO::FETCH_ASSOC);
    $result = generateTree($groups);
    recalculateCountBranchesTree($result);
    //print_r($result);

    if (isset($_GET['group']) && is_numeric($_GET['group']))
    {
        $res = null;
        getBranchTreeById($result, $_GET['group'], $res);
        //print_r($res);
        $ids = null;
        getIdsFromBranchTree($res, $ids);
        //print_r($ids);

        $products = $con->query("select `name` from `products` where `id_group` in " . "(" . implode(',', $ids) . ")")->fetchAll(PDO::FETCH_ASSOC);
    }
    else
    {
        $products = $con->query("select `name` from `products`")->fetchAll(PDO::FETCH_ASSOC);
    }
    //"select `groups`.id, `groups`.id_parent, `groups`.name, count(`count`) as `count` from `groups` join `groups` on `groups`.id = `groups`.id_parent group by `groups`.id_parent"
}
catch (PDOException $e)
{
    print_r($e->getMessage());
}

//$r = new RenderTemplate(['data' => $data], 'accordeon_index.php');
//echo $r->render();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="jquery.accordion.js"></script>
    <script src="jquery.cookie.js"></script>
    <script>

        function getCookie(name) {
            let matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        function setCookie(name, value) {
            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
        }

        window.onload = function ()
        {
            let elem = $('#accordion');
            $('#all-products')[0].addEventListener("click", function (e) {
                setCookie('dcjq-accordion', '');
            }, false);
            //let links;

            // let getLinks = getCookie('links');
            // if (!getLinks)
            // {
            //     setCookie('links', '/');
            // }
            elem[0].addEventListener("click", function (e) {
                if (e.target.classList.contains('dcjq-parent') && !e.target.classList.contains('active'))
                {
                    let last = $(e.target).parent().parent().parent()[0].firstElementChild;
                    // let last1 =$(e.target).parents('li:first-child')
                    //     .map(function() {
                    //         return this.firstElementChild;
                    //     }).get().join( ", " );
                    // if (last1 == nulllast1.length > 1)
                    // {
                    //     last1 = last1.pop().firstElementChild;
                    // }
                    // console.log(last1);
                    e.preventDefault();
                    if (last == null || !last.getAttribute('href'))
                    {
                        document.location.href = '/accordeon_index.php';
                    }
                    else
                    {
                        document.location.href = last.href;
                    }

                    // let getLinks = getCookie('links');
                    // if (getLinks)
                    // {
                    //     links = getLinks.split(',');
                    //     links.push(e.target.href);
                    //     setCookie('links', links.join(','));
                    // }
                }
                else
                {
                    // if(e.target.classList.contains('dcjq-parent'))
                    // {
                    //     let getLinks = getCookie('links');
                    //     if (getLinks)
                    //     {
                    //         links = getLinks.split(',');
                    //         links.pop();
                    //         let lastLink = links[links.length - 1];
                    //         setCookie('links', links.join(','));
                    //         e.preventDefault();
                    //         document.location.href = lastLink;
                    //     }
                    // }
                }
            }, false);
            elem.dcAccordion(
                {
                    disableLink: false
                }
            );
        };

    </script>
    <title>Title</title>
</head>
<body>
    <table>
        <thead>
            <a id="all-products" href="/accordeon_index.php">Все товары</a>
        </thead>
        <tr>
            <td>
                <ul id="accordion">
                    <?php echo generateMenu($result); ?>
                </ul>
            </td>

<!--            <td>-->
<!--                <ul>-->
<!--                    --><?php //foreach ($groups as $group): ?>
<!--                        <li>-->
<!--                            <a href="/?group=--><?//= $group['id'] ?><!--">--><?//= $group['name'] ?><!--</a> --><?//= $group['count'] ?>
<!--                        </li>-->
<!--                    --><?php //endforeach; ?>
<!--                </ul>-->
<!--            </td>-->
            <td>
                <?php foreach ($products as $product): ?>
                    <?= $product['name'] ?>
                    <br>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
</body>
</html>