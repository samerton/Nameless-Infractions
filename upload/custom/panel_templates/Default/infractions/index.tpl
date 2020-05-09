{include file='header.tpl'}
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    {include file='navbar.tpl'}
    {include file='sidebar.tpl'}
	
	<div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">{$INFRACTIONS}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                            <li class="breadcrumb-item active">{$INFRACTIONS}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
		
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                {if isset($NEW_UPDATE)}
                {if $NEW_UPDATE_URGENT eq true}
                <div class="alert alert-danger">
                    {else}
                    <div class="alert alert-primary alert-dismissible" id="updateAlert">
                        <button type="button" class="close" id="closeUpdate" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    {/if}
                    {$NEW_UPDATE}
                    <br />
                    <a href="{$UPDATE_LINK}" class="btn btn-primary" style="text-decoration:none">{$UPDATE}</a>
                    <hr />
                    {$CURRENT_VERSION}<br />
                    {$NEW_VERSION}
                </div>
                {/if}
				
                <div class="card">
                    <div class="card-body">
                        {if isset($SUCCESS)}
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h5><i class="icon fa fa-check"></i> {$SUCCESS_TITLE}</h5>
                                {$SUCCESS}
                            </div>
                        {/if}

                        {if isset($ERRORS) && count($ERRORS)}
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h5><i class="icon fas fa-exclamation-triangle"></i> {$ERRORS_TITLE}</h5>
                                <ul>
                                    {foreach from=$ERRORS item=error}
                                        <li>{$error}</li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}

						<form action="" method="post">
							<div class="form-group">
								<label for="link_location">{$LINK_LOCATION}</label>
								<select class="form-control" id="link_location" name="link_location">
									<option value="1"{if $LINK_LOCATION_VALUE eq 1} selected{/if}>{$LINK_NAVBAR}</option>
									<option value="2"{if $LINK_LOCATION_VALUE eq 2} selected{/if}>{$LINK_MORE}</option>
									<option value="3"{if $LINK_LOCATION_VALUE eq 3} selected{/if}>{$LINK_FOOTER}</option>
									<option value="4"{if $LINK_LOCATION_VALUE eq 4} selected{/if}>{$LINK_NONE}</option>
								</select>
							</div>
							<div class="form-group">
								<label for="inputPlugin">{$PLUGIN}</label>
								<select class="form-control" id="inputPlugin" name="plugin">
                                    {foreach from=$PLUGIN_OPTIONS item=item}
                                        <option value="{$item.value}"{if $PLUGIN_VALUE eq $item.value} selected{/if}>{$item.name}</option>
                                    {/foreach}
								</select>
							</div>
							<hr />
							<strong>{$DATABASE_SETTINGS}</strong>
							<div class="form-group">
								<label for="inputHost">{$ADDRESS}</label>
                                <input class="form-control" type="text" name="host" value="{$ADDRESS_VALUE}" id="inputAddress">
							</div>
							<div class="form-group">
								<label for="inputName">{$NAME}</label>
                                <input class="form-control" type="text" name="name" value="{$NAME_VALUE}" id="inputName">
							</div>
							<div class="form-group">
								<label for="inputUsername">{$USERNAME}</label>
                                <input class="form-control" type="text" name="username" value="{$USERNAME_VALUE}" id="inputUsername">
							</div>
							<div class="form-group">
								<label for="inputPort">{$PORT}</label>
                                <input class="form-control" type="text" name="port" value="{$PORT_VALUE}" id="inputPort">
							</div>
							<div class="form-group">
								<label for="inputPassword">{$PASSWORD}</label>
                                <span class="badge badge-info"><i class="fa fa-question-circle"
									data-container="body"
                                    data-toggle="popover"
                                    title="{$INFO}"
                                    data-content="{$PASSWORD_HIDDEN}"></i></span>
                                <input class="form-control" type="password" name="password" id="inputPassword">
                            </div>
							<div class="form-group">
								<input type="hidden" name="token" value="{$TOKEN}">
								<input type="submit" class="btn btn-primary" value="{$SUBMIT}">
                            </div>
						</form>
						
					</div>
				</div>	
            </div>
        </section>
	</div>
	
    {include file='footer.tpl'}

</div>
<!-- ./wrapper -->

{include file='scripts.tpl'}

</body>
</html>
