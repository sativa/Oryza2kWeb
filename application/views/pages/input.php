<style type="text/css">
    #editor {
        position: relative !important;
        border: 1px solid lightgray;
        margin: auto;
        height: 300px;
        width: 70%;
    }

    ul.scroll-menu {
        position: relative;
        display: inherit !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        -moz-overflow-scrolling: touch;
        -ms-overflow-scrolling: touch;
        -o-overflow-scrolling: touch;
        overflow-scrolling: touch;
        top: 0 !important;
        left: 0 !important;
        width: 100%;
        height: auto;
        max-height: 100px;
        margin: 0;
        border-left: none;
        border-right: none;
        -webkit-border-radius: 0 !important;
        -moz-border-radius: 0 !important;
        -ms-border-radius: 0 !important;
        -o-border-radius: 0 !important;
        border-radius: 0 !important;
        -webkit-box-shadow: none;
        -moz-box-shadow: none;
        -ms-box-shadow: none;
        -o-box-shadow: none;
        box-shadow: none;
    }
</style>
<link rel="stylesheet" href="<?= base_url() ?>css/datepicker.css">

<div class="page-header">
    <h1>ORYZA 2K Potential Yield Information</h1>
</div>


<div class="tabbable">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#param" data-toggle="tab">Parameters</a></li>
        <li><a href="#advanced" data-toggle="tab">Advanced Input</a></li>
        <li><a href="#run" data-toggle="tab" id="tab-run">Run</a></li>
    </ul>
    <div class="tab-content">
        <div id="param" class="tab-pane active">
            <div class="row-fluid">
                <div class="span6">
                    <form name="main" id="main" method="get" class="form-horizontal">
                        <div class="control-group" id="site-field" title="The weather data by location and year">
                            <input type="hidden" id="site" name="site" value="phil">
                            <input type="hidden" id="year" name="year" value="<?= $first_year['year'] ?>">
                            <label class="control-label" for="site-control">Location and Year</label>

                            <div class="controls">
                                <div id="site-control" class="btn-group">
                                    <ul class="dropdown-menu scroll-menu">
                                        <?php for ($i = 0; $i < count($weather_years); $i++): $weather_year = $weather_years[$i] ?>
                                            <li<?= $i == 0 ? ' class="active"' : '' ?>><a
                                                    href="#!site=<?= $weather_year['country_code'] ?>&year=<?= $weather_year['year'] ?>"><?= "{$weather_year['country']} ({$weather_year['year']})" ?></a>
                                            </li>
                                        <?php endfor ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="control-group" id="variety-field" title="The variety">
                            <input type="hidden" id="variety" name="variety" value="0">
                            <label class="control-label" for="variety-control">Variety</label>

                            <div class="controls">
                                <div id="variety-control" class="btn-group">
                                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                        <span id="variety-value"><?= $template[0]['label'] ?></span> <span
                                            class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php for ($i = 0; $i < count($template); $i++): $variety = $template[$i] ?>
                                            <li<?= $i == 0 ? ' class="active"' : '' ?>><a href="#!variety=<?= $i ?>">
                                                    <?= $variety['label'] ?>
                                                </a>
                                            </li>
                                        <?php endfor ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="control-group" id="sowing-field"
                             title="The date of sowing, which is a day of the year from 1&ndash;365 or 1&ndash;366">
                            <label class="control-label" for="sowing">Date of sowing</label>

                            <div class="controls">
                                <input type="text" id="dp3" name="sowing" value="">
                            </div>
                        </div>

                        <div class="control-group" id="cstab-field"
                             title="The crop establishment, either direct seeding or transplanted">
                            <input type="hidden" id="cstab" name="cstab" value="d">
                            <label class="control-label" for="cstab-control">Crop establishment</label>

                            <div class="controls">
                                <div id="cstab-control" class="btn-group">
                                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                                        <span id="cstab-value">Direct Seeding</span> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="active"><a href="#!cstab=d">Direct Seeding</a></li>
                                        <li><a href="#!cstab=t">Transplanted</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="control-group" id="transpl-field" title="Transplanting day">
                            <label class="control-label" for="transpl">Transplanting day</label>

                            <div class="controls">
                                <input id="transpl" name="transpl" type="text">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="span6">
                    <h2>Simulator</h2>
                    <p>This form enables you to calculate the estimated potential yield for a specified location
                        on a simulated weather data based on the specified location and year, for a specific variety,
                        on a specified day of sowing, and method of establishment.</p>
                    <p>Click on the tab <b>Run</b> to see immediate results.</p>
                    <hr />
                    <p>*for advanced input parameters, use the <b>Advanced Input</b> tab</p>
                </div>
            </div>
        </div>
        <div id="advanced" class="tab-pane">
            <form class="form-horizontal">
                <input type="hidden" id="variety-edit" name="variety-edit" value="<?= 0 ?>">

                <div id="variety-edit-control" class="btn-group">
                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#" data-loading-text="Loading...">
                        <span id="variety-edit-value"><?= $template[0]['label'] ?></span> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" id="variety-edit-dropdown">
                        <?php for ($i = 0; $i < count($template); $i++): $variety = $template[$i] ?>
                            <li<?= $i == 0 ? ' class="active"' : '' ?>><a href="#!variety-edit=<?= $i ?>">
                                    <?= $variety['label'] ?>
                                </a>
                            </li>
                        <?php endfor ?>
                    </ul>
                </div>
                <input type="hidden" id="template" name="template" value="control_dat">
                <input type="hidden" id="preset" name="preset" value="true">

                <div class="row-fluid">
                    <div class="span3">
                        <ul id="template-sidebar" class="nav nav-list">
                            <li class="nav-header">Control files</li>
                            <li class="active"><a href="#!template=control_dat">Control data</a></li>
                            <li><a href="#!template=reruns_dat">Rerun data</a></li>
                            <li class="nav-header">Data files</li>
                            <li><a href="#!template=crop_data_dat">Crop data</a></li>
                            <li><a href="#!template=experiment_data_dat">Experimental data</a></li>
                            <li class="nav-header">Description</li>
                            <li><textarea id="variety-desc" style="width: 95%"></textarea></li>
                            <li class="nav-header">Save</li>
                            <li class="no-select" id="save"><a href="#!save">Save</a></li>
                            <li class="no-select"><a href="#!save_as">Save As...</a></li>
                        </ul>
                    </div>
                    <div class="span3" id="editor"><? //echo html_escape($template[0]['control_dat']) ?></div>
                </div>
                <div style="height: 96px"></div>
            </form>
        </div>
        <div id="run" class="tab-pane">
            <div class="container fill">
                <div class="row">
                    <div class="span9">
                <h2>Potential Yield for Sample Run</h2>
                <canvas id="temp-chart" width="700px" height="500px"></canvas>

                    </div>
                    <div class="span3">
                        <br />
                        <br />
                        <br />
                        <br />
                        <p>This graph shows the yield throughout the year by month.</p>
                        <p>Note: <strong>Yield</strong> is measured in <strong>Metric Tons per Hectare</strong></p>
                        <p>(1000 kg / 10000 sq. m.)</p>
                    </div>
                </div>
            </div>
            <div id="wrr14-chart-container">
                <canvas id="wrr14-chart" width="500" height="300"></canvas>
                <!-- Condition: if date of sowing == NULL or 0 -->
                <!-- day (X) x WRR14 (Y) (1-365) -->

                <!-- Condition: if date of sowing is 1-366 -->
                <!-- start day (X) x WRR14 (Y) (start day) till end of output -->
            </div>
        </div>
    </div>
</div>

<!--
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?= base_url() ?>js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
-->
<script src="<?= base_url() ?>js/vendor/jquery-1.9.1.min.js"></script>
<script src="<?= base_url() ?>js/vendor/jquery.ba-hashchange.min.js"></script>
<!-- <script src="<?= base_url() ?>js/plugins.js"></script> -->
<script src="<?= base_url() ?>js/vendor/Chart.js-master/Chart.min.js"></script>
<script src="<?= base_url() ?>js/vendor/require.js"></script>
<script src="<?= base_url() ?>js/vendor/ace-builds/src-noconflict/ace.js" type="text/javascript"></script>
<script src="<?= base_url() ?>js/vendor/ace-builds/src-noconflict/mode-oryza_dat.js"></script>

<script>
var buffer = [];
var editor;

(function () {
    var currItem;
    var prevItem;

    var isChangedVariety = false;
    var cancelHashEvent = false;
    var lastVariety = "";

    function isTransplanted() {
        return $("#cstab").val() == "t";
    }

    function doLogic() {
        displayTransplField(isTransplanted());

        if (isChangedVariety) {
            loadTemplate($("#variety-edit").val());
            isChangedVariety = false;
            $("#variety-edit-value").html(lastVariety);
        }
        refreshEditor();
    }

    function displayTransplField(b) {
        var transplField = $("#transpl-field");

        transplField.clearQueue();
        if (b)
            transplField.slideDown(250);
        else
            transplField.slideUp(250);
    }

    function validate() {
        var valid = true;

        valid &= $("#site").val() != "";
        valid &= $("#year").val() != "";
        valid &= $("#variety").val() != "";
        valid &= $("#cstab").val() != "";

        if (isTransplanted())
            valid &= $("#transpl").val() != "";

        return valid;
    }

    function loadTemplate() {
        var templates = ["description", "control_dat", "reruns_dat", "crop_data_dat", "experiment_data_dat", "preset"];
        buffer = [];
        var index = $("#variety-edit").val();
        for (var t in templates) {
            $.ajax("<?= base_url() ?>index.php/input/retrieve_template/" + index + "/" + templates[t],
                {
                    async: false,
                    type: 'GET'
                }).done(function (value) {
                    switch (templates[t]) {
                        case "description":
                        case "preset":
                            buffer[templates[t]] = {
                                "value": value,
                                "isModified": false
                            };
                            break;
                        default:
                            var mode = editor.getSession().getMode();
                            var session = ace.createEditSession(value, mode);
                            buffer[templates[t]] = {
                                "session": session,
                                "isModified": false
                            };
                            session.on('change', function () {
                                buffer[templates[t]].isModified = true;
                            });
                    }
                    $("#variety-desc").on('change', function () {
                        buffer["description"].value = $(this).val();
                        buffer["description"].isModified = true;
                    });
                });
        }
    }

    function readyEditor() {
        editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
        editor.getSession().setMode("ace/mode/oryza_dat");
        loadTemplate($("#variety-edit").val());
        refreshEditor();
    }

    function run() {
        if (!validate()) {
            alert("Invalid input");
        }
        else {
            var ctx = document.getElementById("temp-chart").getContext("2d");
            $.getJSON('<?= base_url() ?>index.php/input/parse_weather_data/' + $("#site").val() + '/' + $("#year").val(), function (json) {
                var day = [], min = [], max = [], avg = [];
                for (var i in json) {
                    day.unshift(Number(json[i][2]));
                    min.unshift(Number(json[i][4]));
                    max.unshift(Number(json[i][5]));
                    avg.unshift(Number(json[i][6]));
                }

                var extremes = [
                    {
                        fillColor: "rgba(204, 0, 0, 0.5)",
                        strokeColor: "rgba(102, 0, 0, 1.0)",
                        pointColor: "rgba(102, 0, 0, 1.0)",
                        pointStrokeColor: "rgba(102, 0, 0, 1.0)",
                        data: max
                    },
                    {
                        fillColor: "rgba(255, 255, 255, 0.0)",
                        strokeColor: "rgba(102, 0, 102, 0.5)",
                        pointColor: "rgba(102, 0, 102, 0.5)",
                        pointStrokeColor: "rgba(102, 0, 102, 0.5)",
                        data: avg
                    },
                    {
                        fillColor: "rgba(255, 255, 255, 1.0)",
                        strokeColor: "rgba(0, 0, 102, 1.0)",
                        pointColor: "rgba(0, 0, 102, 1.0)",
                        pointStrokeColor: "rgba(0, 0, 102, 1.0)",
                        data: min
                    }
                ];

                var tempChart = new Chart(ctx).Line({
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: extremes
                }, {
                    // options
                });
            });
            // use the parameters given in parameters section
            //$("#main").submit();
        }
    }

    // Repaints the whole Advanced Input section
    function refreshEditor() {
        editor.setSession(buffer[$("#template").val()].session);
        $("#variety-desc").val(buffer["description"].value);

        if ($("#preset").val()) {
            $("#save").hide();
        }
        else {
            $("#save").show();
        }

        $("#variety-desc").attr("disabled", $("#preset").val());
    }

    function isExisting(varietyName) {
        // TODO determine if variety is existing
        return false;
    }

    function save(filename) {
        var varietyToSave = {
            label: filename,
            file_prefix: filename.toLowerCase().replace(' ', '_'),
            description: buffer["description"].value,
            control_dat: buffer["control_dat"].session.getValue(),
            reruns_dat: buffer["reruns_dat"].session.getValue(),
            crop_data_dat: buffer["crop_data_dat"].session.getValue(),
            experiment_data_dat: buffer["experiment_data_dat"].session.getValue(),
            preset: 0
        };
        var success = false;
        $.ajax("<?= base_url() ?>index.php/input/save_template",
            {
                type: 'POST',
                data: varietyToSave
            }).done(function () {
                for (var t in buffer)
                    buffer[t].isModified = false;
                success = true;
            }).error(function () {
                success = false;
            });
        return success;
    }

    function saveAs() {
        var filename = prompt("Enter new variety");
        if (filename != "")
            if (isExisting(filename) && confirm("Are you sure you want to overwrite this variety?") || !isExisting(filename))
                return save(filename);
        return false;
    }

    function invokeEvent(id) {
        switch (id.toLowerCase()) {
            case "save":
                save();
                break;
            case "save_as":
                saveAs();
                break;
        }
    }

    // Default hook for hash events
    $(window).hashchange(function () {
        if (cancelHashEvent) {
            cancelHashEvent = false;
            return;
        }

        var rawHash = location.hash.substr(1);
        var isHashEvent = rawHash.indexOf('!') == 0; // event data k/v pair(s) are specified after bang character.

        if (isHashEvent) {
            rawHash = rawHash.substr(1);
            var segments = rawHash.split('&');
            for (var i in segments) {
                var segment = segments[i];
                var pairDivider = segment.indexOf('=');
                var isSegmentPair = pairDivider != -1;

                if (isSegmentPair) { // change variables
                    var key = segment.substr(0, pairDivider);
                    var value = segment.substr(pairDivider + 1);

                    try {
                        $("#" + key).val(value);
                        $("#" + key + "-value").html(currItem.text());
                    } catch (e) {
                    }
                }
                else { // invoke named event
                    invokeEvent(segment);
                }

                doLogic();
            }
        }
    });

    // Default hook for dropdowns
    $(".dropdown-menu").find("li").find("a").click(function () {
        prevItem = $(this).parent().parent().find(".active");
        currItem = $(this).parent();

        prevItem.removeClass("active");
        currItem.addClass("active");
    });

    $("#tab-run").click(function () {
        run();
    });

    $("#template-sidebar").find("li").not(".no-select").find("a").click(function () {
        $(this).parent().parent().find("li").not(".no-select").removeClass("active");
        if (!$(this).hasClass(".no-select"))
            $(this).parent().addClass("active");
    });

    $("#variety-edit-dropdown").find("li").find("a").click(function () {
        var varietyModified = false;
        for (var t in buffer)
            varietyModified |= buffer[t].isModified;

        lastVariety = $(this).html();
        $("#variety-edit-value").html("Please wait...");
        if (varietyModified && !confirm("Are you sure you want to switch to another variety? Your changes in this variety will not be saved.")) {
            cancelHashEvent = true;
            prevItem.addClass('active');
            currItem.removeClass('active');
            currItem = prevItem;
        }
        else
            isChangedVariety = true;
    });

    $(document).ready(function () {
        $("#transpl-field").hide();
        location.hash = "";
        readyEditor();
    })
})();
</script>
<script>
    Date.prototype.getDOY = function() {
        var onejan = new Date(this.getFullYear(),0,1);
        return Math.ceil((this - onejan) / 86400000);
    }

    if (top.location != location) {
        top.location.href = document.location.href ;
    }
    $(function(){
        $('#dp1').datepicker({
            format: 'mm-dd-yyyy'
        });
        $('#dp2').datepicker();
        $('#dp3').datepicker();
        $('#dp3').datepicker();
        $('#dpYears').datepicker();
        $('#dpMonths').datepicker();


        var startDate = new Date(2012,1,20);
        var endDate = new Date(2012,1,25);
        $('#dp4').datepicker()
            .on('changeDate', function(ev){
                if (ev.date.valueOf() > endDate.valueOf()){
                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    startDate = new Date(ev.date);
                    $('#startDate').text($('#dp4').data('date'));
                }
                $('#dp4').datepicker('hide');
            });
        $('#dp5').datepicker()
            .on('changeDate', function(ev){
                if (ev.date.valueOf() < startDate.valueOf()){
                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    endDate = new Date(ev.date);
                    $('#endDate').text($('#dp5').data('date'));
                }
                $('#dp5').datepicker('hide');
            });
    });
</script>
<script src="<?= base_url() ?>js/bootstrap-datepicker.js"></script>
