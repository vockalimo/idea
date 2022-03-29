<?php
$deskcontent = [];
// 牌組
$file = "./cardsdata/base.json";
$data = file_get_contents($file);
$obj = json_decode($data);
$deskcontent = $obj;

?>

<html>
<body>
<div> menu
    <button id="exportImage">輸出檔案</button>
    <button id="zoomInBtn">放大</button>
    <button id="zoomOutBtn">縮小</button>
    目前縮放: <input type="text" readonly value="35%" id="zoom" style="border:3px solid #FFFFFF">
</div>
<canvas id="canvas" width="1100" height="600"></canvas>

<input type="hidden" name="deskcontent" id="deskcontent" value='<?php echo json_encode($deskcontent) ?>'>
<script src="https://cdn.bootcdn.net/ajax/libs/fabric.js/521/fabric.js"></script>

<script>
    let jsonObj = JSON.parse(document.getElementById('deskcontent').value);
    console.log(" json " + jsonObj.cards);

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
            console.log(" ratio" + ratio);
            let dataURL = canvas.toDataURL({
                format: 'png',
                quality: 0.8,
                multiplier: 1,
            });
            downloadname = 'canvas.png'
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
    let modevar = 10;


    jsonObj.cards.forEach(
        function (item, index) {
            console.log(index)
            console.log(item)
        }
    )

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
                    left: width + 190,
                    top: height + 80,
                    originX: 'center',
                    originY: 'center',
                    left: 140,
                    top: 450
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


                let group = new fabric.Group([oImg, circle, numtext, text], {
                    left: width + 20,
                    top: height + 20
                });
                canvas.add(group)


            },  {crossOrigin: 'anonymous'})


        }
    )
    let itext = new fabric.IText("雷丘逐電犬牌組", {
        left: 2000,
        top: 1100,
        fontSize: 80
    })
    canvas.add(itext)


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
