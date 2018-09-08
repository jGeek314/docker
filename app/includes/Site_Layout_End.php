        
    </div> <!--- .container-fluid --->
</div>
<!---
<div class="site-footer conainer-fluid bg-dark text-light text-center">
    <?php $ThisCopyWright = date("M Y");?>
    Copyright &copy; <?php echo "$ThisCopyWright" ?> Stratton Systems Design
    <span class="small">[<?php echo getDB() ?>]</span>
</div>
--->
</main>








<!--- Scripts needed for bootstrap 4 --->
<script
    src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
<script 
    src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" 
    integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" 
    crossorigin="anonymous"></script>
<script 
    src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" 
    integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" 
    crossorigin="anonymous"></script>

<!--- Scripts for UI and validate--->
<script
    src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
    integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
    crossorigin="anonymous"></script>

<script 
    src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js" 
    integrity="sha256-yazfaIh2SXu8rPenyD2f36pKgrkv5XT+DQCDpZ/eDao=" 
    crossorigin="anonymous"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-touch-events/1.0.5/jquery.mobile-events.js"></script>

<!---
<script 
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js" 
    integrity="sha256-7Ls/OujunW6k7kudzvNDAt82EKc/TPTfyKxIE5YkBzg=" 
    crossorigin="anonymous"></script> --->


<script src="includes/Site_Javascript.js?v=<?=$appVersion;?>"></script>

<script>
    
    //edit transaction
    $("[data-edit-trans]").click(function(){
        var $editTRN = $(this).attr('data-edit-trans');
        post('TRN_210.php',{action: 'modify', editTRN: $editTRN, returnURL: '<?=$curpage ?>' });
    });
    
    //change account (from header menu dropdown)
    $("[data-change-account]").click(function(){
        var AccountID = $(this).data('change-account');    
        post('TRN_001.php',{AccountID: AccountID, ReturnPage: '<?=$curpage?>'});
    });
    
</script>

<?php unset($_SESSION['HighlightTrans']); ?>

