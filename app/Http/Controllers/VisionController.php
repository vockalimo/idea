<?php

namespace App\Http\Controllers;


use Google\Service\Vision\BatchAnnotateImagesRequest;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PulkitJalan\Google\Facades\Google;

class VisionController extends Controller
{

    /**
     * @var \Google_Service
     */
    private $visionclient;

    public function __construct()
    {
        $this->visionclient = Google::make('vision');
    }

    public function webDetection(Request $request)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Origin, Methods, Content-Type");

        try {
            $validateResult = $this->validate($request, [
                'file' => 'required|image|mimes:jpg,jpeg,png|max:2048'
            ]);
        } catch (ValidationException $e) {
            $validationInstance = $e->validator;
            $errorMessageData = $validationInstance->getMessageBag();
            $errorMessages = $errorMessageData->getMessages();
            return response()->json(["error" => $errorMessages]);
        }

        if($request->file()) {
            $imagecontent = $request->file('file')->get();
            $query = array(
                "image" => array(
                    "content" => base64_encode($imagecontent)
                ),
                "features" => array(
                    "type" => "WEB_DETECTION",
                    "maxResults" => 10
                ),

            );
            $batchrequest = new BatchAnnotateImagesRequest();
            $batchrequest->setRequests($query);
            $response = $this->visionclient->images->annotate($batchrequest);

            return response()->json($response->getResponses());
        } else {
            return response()->json(['success'=>'File not uploaded successfully.']);
        }

    }

    public function webDetectionRender(Request $request) {
        return view('webdetectionsearch');
    }

    public function ProductSearchSimilar(Request $request) {


            try {
                $validateResult = $this->validate($request, [
                    'file' => 'required|image|mimes:jpg,jpeg,png|max:2048'
                ]);
            } catch (ValidationException $e) {
                $validationInstance = $e->validator;
                $errorMessageData = $validationInstance->getMessageBag();
                $errorMessages = $errorMessageData->getMessages();
                return response()->json(["error" => $errorMessages]);
            }



        if($request->file()) {
         $imagecontent = $request->file('file')->get();
            $query = array(
                "image" => array(
                    "content" => base64_encode($imagecontent)
                ),
                "features" => array(
                    "type" => "PRODUCT_SEARCH",
                    "maxResults" => 5
                ),
                "imageContext" => array(
                    "productSearchParams" => array(
                        "productSet" => "projects/image-search-341607/locations/asia-east1/productSets/product_set0",
                        "productCategories" => array(
                            "apparel-v2",
                        ),
                        //    "filter" => "style=womens OR style=women",
                    )
                ),
            );
            $batchrequest = new BatchAnnotateImagesRequest();
            $batchrequest->setRequests($query);
            $response = $this->visionclient->images->annotate($batchrequest);

            return response()->json($response->getResponses());
        } else {
            return response()->json(['success'=>'File not uploaded successfully.']);
        }
    }

    public function ProductSearchSimilarRender(Request $request) {
        return view('visionimagesearch');
    }

    public function ProductSearch(Request $request)
    {
        $batchrequest = new BatchAnnotateImagesRequest();
        $url = "https://s.yimg.com/zp/MerchandiseImages/5FBF7F232A-SP-9804436.jpg";
        $imagecontent = file_get_contents($url);
        $query = array(
            "image" => array(
                "content" => base64_encode($imagecontent)
            ),
            "features" => array(
                "type" => "PRODUCT_SEARCH",
                "maxResults" => 5
            ),
            "imageContext" => array(
                "productSearchParams" => array(
                    "productSet" => "projects/image-search-341607/locations/asia-east1/productSets/product_set0",
                    "productCategories" => array(
                        "apparel-v2",
                    ),
                //    "filter" => "style=womens OR style=women",
                )
            ),
        );
        $batchrequest->setRequests($query);
        $response = $this->visionclient->images->annotate($batchrequest);

        $productSearchResults = array();

        foreach ($response->getResponses() as $data) {
            foreach ($data->getProductSearchResults()->getProductGroupedResults() as $object) {

                $productSearchResultsTmp = array();
                $productSearchResultsTmp['result'] = $this->getProductGroupResult($object->getBoundingPoly()->getNormalizedVertices());
                foreach ($object->getResults() as $result) {
                    $tmp = array();
                    $tmp['image'] = $result->getImage();
                    $tmp['score'] = $result->getScore();
                    $tmp['product']['description'] = $result->getProduct()->getDescription();
                    $tmp['product']['displayName'] = $result->getProduct()->getDisplayName();
                    $tmp['product']['name'] = $result->getProduct()->getName();
                    $tmp['product']['productCategory'] = $result->getProduct()->getProductCategory();

                    foreach ($result->getProduct()->getProductLabels() as $label) {
                        $productLabelTmp = array();
                        $productLabelTmp['key'] = $label->getKey();
                        $productLabelTmp['value'] = $label->getValue();
                        $tmp['product']['productLabels'][] = $productLabelTmp;
                    }
                    $imgresponse = $this->visionclient->projects_locations_products_referenceImages->get($tmp['image']);
                    $tmp['uri'] = $imgresponse->getUri();
                    $gsurl = explode("://", $imgresponse->getUri());
                    $tmp['gsurl'] = $gsurl[1];
                    //https://storage.cloud.google.com/product-search-tutorial/images/468f782e70ba11e8941fd20059124800.jpg
                    $tmp['imgsrc'] = "https://storage.cloud.google.com/" .  $tmp['gsurl'];
                    $productSearchResultsTmp['results'][] = $tmp;
                }

                foreach ($object->getObjectAnnotations() as $result) {
                    $tmp = array();
                    $tmp['languageCode'] = $result->getLanguageCode();
                    $tmp['mid'] = $result->getMid();
                    $tmp['name'] = $result->getName();
                    $tmp['score'] = $result->getScore();
                    $productSearchResultsTmp['objectAnnotations'][] = $tmp;
                }
            }
        }
        $productSearchResults['results'] = $productSearchResultsTmp['results'];
        $productSearchResults['productGroupedResults'] = $productSearchResultsTmp;

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Origin, Methods, Content-Type");
        return response()->json(array(
            "productSearchResults" => $productSearchResults,
        ));

    }


    public function test(Request $request)
    {

        if (Storage::disk('local')->exists('public/uploads/file.jpg')) {
            try {
                $imagecontent = Storage::disk('local')->get('public/uploads/file.png');
                $batchrequest = new BatchAnnotateImagesRequest();
                $query = array(
                    "image" => array(
                        "content" => base64_encode($imagecontent)
                    ),

                    "features" => array(
                        array(
                            "type" => "CROP_HINTS",
                            "maxResults" => 10
                        ),
                        array(
                            "type" => "OBJECT_LOCALIZATION",
                            "maxResults" => 10
                        ),
                        array(
                            "type" => "LABEL_DETECTION",
                            "maxResults" => 10
                        ),
                        array(
                            "type" => "IMAGE_PROPERTIES",
                            "maxResults" => 10
                        ),
                        array(
                            "type" => "LOGO_DETECTION",
                            "maxResults" => 10
                        ),


                    ),
                    "image_context" => array(
                        "crop_hints_params" => array(
                            "aspect_ratios" => array(0.8, 1, 1.33),

                        ),
                    )


                );
                $batchrequest->setRequests($query);
                $response = $this->visionclient->images->annotate($batchrequest);
                $localizedObjectAnnotations = array();
                $CropHintsAnnotations = array();
                $labelAnnotations = array();
                $imagePropertiesAnnotation = array();

                foreach ($response->getResponses() as $data) {

                    $localizedObjectAnnotations = $this->LocalObjectAnnotaions($data);
                    $CropHintsAnnotations = $this->CropHintsAnnotaions($data);
                    $labelAnnotations = $this->LabelAnnotations($data);
                    $imagePropertiesAnnotation = $this->ImagePropertiesAnnoation($data);

                }

                return response()->json(array(
                    "localizedObjectAnnotations" => $localizedObjectAnnotations,
                    "cropHintsAnnotation" => $CropHintsAnnotations,
                    "labelAnnotations" => $labelAnnotations,
                    "imagePropertiesAnnotation" => $imagePropertiesAnnotation

                ));

            } catch (FileNotFoundException $e) {
                log_exception($e);
            }

        } else {
            $localizedObjectAnnotations = array();
            return response()->json($localizedObjectAnnotations);
        }

    }


    //
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png|max:204'
        ]);

        if ($request->file()) {
            $file_name = time() . '_' . $request->file->getClientOriginalName();
            $request->file('file')->storeAs('uploads', $file_name, 'public');
            return response()->json(['success' => 'File uploaded successfully.']);
        }
    }

    /**
     * @param $data
     * @param array $localizedObjectAnnotations
     * @return array
     */
    private function LocalObjectAnnotaions($data): array
    {
        $localizedObjectAnnotations = array();
        foreach ($data->getLocalizedObjectAnnotations() as $localobj) {
            $localizedObjectAnnotationTmp = array();
            $localizedObjectAnnotationTmp['score'] = $localobj->getScore();
            $localizedObjectAnnotationTmp['name'] = $localobj->getName();
            $localizedObjectAnnotationTmp['mid'] = $localobj->getMid();

            $tmp = array();
            foreach ($localobj->getBoundingPoly()->getNormalizedVertices() as $boundingPoly) {
                $tmp[] = array(
                    'x' => $boundingPoly->getX(),
                    'y' => $boundingPoly->getY(),
                );
            }
            $localizedObjectAnnotationTmp['boundingPoly']['normalizedVertices'] = $tmp;
            $localizedObjectAnnotations[] = $localizedObjectAnnotationTmp;
        }
        return $localizedObjectAnnotations;
    }

    /**
     * @param $data
     * @return array
     */
    private function CropHintsAnnotaions($data): array
    {
        $CropHintsAnnotations = array();
        foreach ($data->getCropHintsAnnotation() as $localobj) {
            $localizedObjectAnnotationTmp = array();

            $tmp = array();
            foreach ($localobj->getBoundingPoly()->getVertices() as $boundingPoly) {
                $tmp[] = array(
                    'x' => $boundingPoly->getX(),
                    'y' => $boundingPoly->getY(),
                );
            }
            $localizedObjectAnnotationTmp['boundingPoly']['vertices'] = $tmp;
            $localizedObjectAnnotationTmp['confidence'] = $localobj->getConfidence();
            $localizedObjectAnnotationTmp['importanceFraction'] = $localobj->getImportanceFraction();

            $CropHintsAnnotations['cropHints'][] = $localizedObjectAnnotationTmp;
        }
        return $CropHintsAnnotations;
    }

    /**
     * @param $data
     * @return array
     */
    private function LabelAnnotations($data): array
    {
        $labelAnnotations = array();
        foreach ($data->getLabelAnnotations() as $localobj) {
            $localizedObjectAnnotationTmp = array();
            $localizedObjectAnnotationTmp['description'] = $localobj->getDescription();
            $localizedObjectAnnotationTmp['mid'] = $localobj->getMid();
            $localizedObjectAnnotationTmp['score'] = $localobj->getScore();
            $localizedObjectAnnotationTmp['topicality'] = $localobj->getTopicality();
            $labelAnnotations[] = $localizedObjectAnnotationTmp;

        }
        return $labelAnnotations;
    }

    /**
     * @param $data
     * @return array
     */
    private function ImagePropertiesAnnoation($data): array
    {
        $imagePropertiesAnnotation = array();
        foreach ($data->getImagePropertiesAnnotation() as $localobj) {
            $localizedObjectAnnotationTmp = array();
            $tmp = array();

            foreach ($localobj->getColors() as $colorinfo) {

                $tmp['hex'] = sprintf('%02X%02X%02X',
                    $colorinfo->getColor()->getRed(),
                    $colorinfo->getColor()->getGreen(),
                    $colorinfo->getColor()->getBlue());
                $tmp['color']['alpha'] = $colorinfo->getColor()->getAlpha();
                $tmp['color']['red'] = $colorinfo->getColor()->getRed();
                $tmp['color']['green'] = $colorinfo->getColor()->getGreen();
                $tmp['color']['blue'] = $colorinfo->getColor()->getBlue();
                $tmp['pixelFraction'] = $colorinfo->getPixelFraction();
                $tmp['score'] = $colorinfo->getScore();
                $localizedObjectAnnotationTmp['color'][] = $tmp;
                $imagePropertiesAnnotation['dominantColors']['colors'][] = $tmp;
            }

        }
        return $imagePropertiesAnnotation;
    }

    /**
     * @param $data //object->getBoundingPoly()->getNormalizedVertices()
     * @return array[]
     */
    private function getProductGroupResult($data): array
    {
        $productSearchResultsTmp = array();
        foreach ($data as $normalizedVertex) {
            $tmp = array();
            $tmp[] = array(
                'x' => $normalizedVertex->getX(),
                'y' => $normalizedVertex->getY(),
            );
            $productSearchResultsTmp['boundingPoly']['normalizedVertices'][] = $tmp;
        }
        return $productSearchResultsTmp;
    }
}
