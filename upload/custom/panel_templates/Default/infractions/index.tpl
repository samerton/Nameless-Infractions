{include file='header.tpl'}
<body id="page-top">
<div id="wrapper">
    {include file='sidebar.tpl'}

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            {include file='navbar.tpl'}

            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">{$INFRACTIONS}</h1>
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                        <li class="breadcrumb-item active">{$INFRACTIONS}</li>
                    </ol>
                </div>

                {include file='includes/update.tpl'}

                <div class="card shadow mb-4">
                    <div class="card-body">
                        {include file='includes/alerts.tpl'}

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
                            <div class="form-group">
                                <label for="inputGuestsView">{$GUESTS_VIEW}</label>
                                <input id="inputGuestsView" name="guests_view" type="checkbox" value="1"{if $GUESTS_VIEW_VALUE eq 1} checked{/if} />
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

                <div style="height:1rem;"></div>
            </div>
        </div>

        {include file='footer.tpl'}
    </div>

</div>

{include file='scripts.tpl'}

</body>
</html>