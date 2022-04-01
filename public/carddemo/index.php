<?php
$deskcontent = [];
// 牌組
$file = "./cardsdata/base.json";
$data = file_get_contents($file);
$obj = json_decode($data);
$deskcontent = $obj;

$code = 'qp8356';
if (isset($_GET['s']) && $_GET['s'] != "" ) {
         $code = $_GET['s'];
     }

 $url = 'https://ptcgtw.shop/connect_mysql2.php?type=找牌組&short_url='.$code;
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, $url);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
 $result = curl_exec($ch);



 $data = explode("┤", $result);
 $object = new StdClass();


 $re = '/(?P<url>^https:.*)├張數┐(?P<count>.*)┼價錢┐┼card_type┐(?P<card_type>.*)┼p_color┐(?P<p_color>.*)┼set_name┐(?P<set_name>.*)┼set_no┐(?P<set_no>.*)┼name┐(?P<name>.*)┼price┐(?P<price>\d+)/m';
 foreach ($data as $key => $value) {
         $card = new StdClass();
         preg_match($re, $value, $matches);
         if ( $matches['url'] == '') {
                 continue;
     }
     $card->id = $matches['url'];
     $card->title = $matches['name'];
     $card->catelog = $matches['card_type'];
     $card->catevalue = $matches['card_type'];
     $card->imagesrc  = $matches['url'];
     $card->expansionCodes = $matches['set_name'];
     $card->count = $matches['count'];
     $object->cards[] = $card;
 }


 $deskcontent = $object;




?>

<html>
<body>
<div> menu
    <button id="exportImage">輸出檔案</button>
    <button id="zoomInBtn">放大</button>
    <button id="zoomOutBtn">縮小</button>
    目前縮放: <input type="text" readonly value="35%" id="zoom" style="border:3px solid #FFFFFF">
</div>
<canvas id="canvas" width="1024" height="768"></canvas>

<input type="hidden" name="deskcontent" id="deskcontent" value='<?php echo json_encode($deskcontent) ?>'>
<script src="https://cdn.bootcdn.net/ajax/libs/fabric.js/521/fabric.js"></script>

<script>
    let jsonObj = JSON.parse(document.getElementById('deskcontent').value);

    const $ = (id) => document.getElementById(id)
    const drawBtn = $('drawBtn')
    const distanceInput = $('distanceInput')
    const addRectBtn = $('addRectBtn')
    const zoomInBtn = $('zoomInBtn')
    const zoomOutBtn = $('zoomOutBtn')
    const showZoom = $('zoom')
    const exportImage = $('exportImage')
    let canvas = new fabric.Canvas('canvas',
        {backgroundColor: "white",}
    );
    zoomOutBtn.addEventListener('click', () => setZoom(-0.1))
    zoomInBtn.addEventListener('click', () => setZoom(+0.1))
    exportImage.addEventListener('click', (e) => {

            let ratio = window.devicePixelRatio;
            let dataURL = canvas.toDataURL({
                format: 'jpg',
                quality: 0.6,
                //multiplier: 1,
            });
            downloadname = 'canvas.jpg'
            downloadURI(dataURL, downloadname);
            console.log(" download......");


        }
    )

    function downloadURI(uri, name) {
        var link = document.createElement('a');
        link.download = name;
        link.href = uri;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        delete link;
    }


    let zoomdata = canvas.getZoom();
    canvas.setZoom(0.35);



    let basewidth = 300;
    let baseheight = 400;
    let offbaseheight = 500;
    let modevar = 9;






    let group = "";
    jsonObj.cards.forEach(
        function (item, index) {
            let mFirst = parseInt(index / modevar);
            let wFirst = parseInt(index % modevar);
            let width = basewidth * wFirst;
            let height = offbaseheight * mFirst;


            fabric.Image.fromURL(item.imagesrc, (img) => {

                const oImg = img.set({
                }, {});
                oImg.scaleToWidth(basewidth);
                oImg.scaleToHeight(baseheight)

                let text = new fabric.Text(item.title, {
                    originX: 'center',
                    originY: 'center',
                    left: 140,
                    top: 450,
                    fontSize: 30
                })

                let circle = new fabric.Circle({
                    radius: 45,
                    fill: '#eef',
                    originX: 'center',
                    originY: 'center',
                    opacity: 0.8,
                    hasBorder: true,
                    //strokeDashArray: [10,10],
                    stroke: 'black',
                    strokeWidth: 4,
                    left: 140,
                    top: 380
                });

                let numtext = new fabric.IText(item.count, {
                    fontSize: 50,
                    fill: 'red',
                    originX: 'center',
                    originY: 'center',
                    left: 140,
                    top: 380
                });


                group = new fabric.Group([oImg, circle, numtext, text], {
                    left: width + 20,
                    top: height + 20
                });

                canvas.add(group)


            },  {crossOrigin: 'anonymous'})


        }
    )
    quo = Math.floor(jsonObj.cards.length/modevar);

    remainder = jsonObj.cards.length % modevar;

    left_offset = basewidth * ( remainder + 1);
    top_offset = ( offbaseheight  ) * ( quo  );
    let itext = new fabric.IText("雷丘逐電犬牌組-編輯內容", {
        left: left_offset ,
        top: top_offset + 50   ,
        fontSize: 80
    })

    let itextbox = new fabric.Textbox("編輯內容, 比如牌組的特性", {
            left: left_offset,
            top: top_offset + 170,
            fontSize: 40
    })



    canvas.add(itext)
    canvas.add(itextbox)


    function setZoom(zoom) {
        // zoom 為 +-0.1
        const newZoom = canvas.getZoom() + zoom

        canvas.setZoom(newZoom)
        // showZoom 為 input element
        showZoom.value = `${Math.round(newZoom * 100)}%`
    }



</script>
</body>
</html>
