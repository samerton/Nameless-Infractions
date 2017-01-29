{include file='navbar.tpl'}

<div class="container" style="padding-top: 5rem;">
  <div class="card">
    <div class="card-block">
      <h2 style="display:inline;">{$INFRACTIONS}</h2>
	  <span class="pull-right">
	    <form class="form-inline" action="" method="post">
		  <input type="hidden" name="token" value="{$TOKEN}">
		
		  <div class="input-group">
		    <label class="sr-only" for="inputUsername">{$SEARCH}</label>
		    <input type="text" id="inputUsername" name="search" class="form-control" placeholder="{$SEARCH}">
		    <span class="input-group-btn">
			  <button class="btn btn-primary" type="button"><i class="fa fa-search" aria-hidden="true"></i></button>
		    </span>
		  </div>

		</form>
	  </span>
	  
	  <hr />
	  
	  <div class="table-responsive">
	    <table class="table table-striped table-bordered">
		  <colgroup>
		    <col span="1" style="width: 15%;">
		    <col span="1" style="width: 15%;">
		    <col span="1" style="width: 15%">
			{if !isset($BU)}
			<col span="1" style="width: 15%">
			<col span="1" style="width: 30%">
			{else}
			<col span="1" style="width: 45%">
			{/if}
			<col span="1" style="width: 10%">
		  </colgroup>

		  <thead>
		    <tr>
		      <th>{$USERNAME}</th>
			  <th>{$STAFF_MEMBER}</th>
			  {if !isset($BU)}
			  <th>{$ISSUED}</th>
			  {/if}
			  <th>{$ACTION}</th>
			  <th>{$REASON}</th>
			  <th></th>
		    </tr>
		  </thead>

		  <tbody>
		    {foreach from=$INFRACTIONS_LIST item=infraction}
		    <tr>
		      <td><a href="{$infraction.username_link}" style="{$infraction.username_style}">{$infraction.username}</a></td>
		      <td><a href="{$infraction.staff_member_link}" style="{$infraction.staff_member_style}">{$infraction.staff_member}</a></td>
		      <td><span data-toggle="tooltip" data-trigger="hover" data-original-title="{$infraction.issued_full}">{$infraction.issued}</span></td>
		      <td>
			    {if $infraction.action_id == 1 || $infraction.action_id == 2}<span class="tag tag-danger">{$infraction.action}</span>{/if}
				
				{if $infraction.action_id == 2}
				  {if $infraction.revoked == 1}
				    <span class="tag tag-success">{$infraction.status}</span>
				  {else}
				    <span data-toggle="tooltip" data-trigger="hover" data-original-title="{$infraction.expires_full}" class="tag tag-danger">{$infraction.status}</span>
				  {/if}
				{/if}
			  </td>
		      <td>{$infraction.reason}</td>
		      <td><a href="{$infraction.view_link}" class="btn btn-primary">{$VIEW} &raquo;</a></td>
		    </tr>
		    {/foreach}
		  </tbody>
        </table>
		
		{$PAGINATION}
	  </div>
	</div>
  </div>
</div>

{include file='footer.tpl'}