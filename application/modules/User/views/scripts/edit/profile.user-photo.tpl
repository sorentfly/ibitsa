<div class="generic_layout_container user_photo"<? if($this->tabname !== 'user_photo'):?> style="display: none;"<? endif;?>>
    <form action="/members/edit/profile/user_photo/?action=edit" enctype="multipart/form-data" class="global_form" id="EditPhoto" method="post">
        <h4><?=$this->translate('It is recommended to use your real photo');?></h4>
        <table class="user_photo">
            <tbody>
                <tr>
                    <th class="current_avatar"><?=$this->translate('Current Photo');?></th>
                    <th class="middle_cell" style="display: none;"></th>
                    <th class="new_avatar" style="display: none;"><?=$this->translate('New Photo');?></th>
                </tr>
                <tr>
                    <td class="current_avatar">
                        <? $thumb_src = $this->user->getPhotoUrl('thumb.profile'); ?>
                        <? if($this->user->photo_id > 0 && $thumb_src != null): ?>
                            <img alt="" class="item_photo_user thumb_profile" id="lassoImg" src="<?=$this->user->getPhotoUrl('thumb.profile');?>"/>
                        <? else: ?>
                            <img alt="" class="item_photo_user thumb_profile item_nophoto" id="lassoImg" src="/application/modules/User/externals/images/nophoto_user_thumb_profile.png"/>
                        <? endif; ?>
                    </td>
                    <td class="current_new middle_cell" style="display: none;"><img height="36" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMzZweCIgaGVpZ2h0PSIzNnB4IiB2aWV3Qm94PSIwIDAgMTYgMTYiIHZlcnNpb249IjEuMSI+PHBhdGggZD0iTTE1NCwxMDcgTDE0NCwxMDcgTDE0NCwxMDEgTDE1NCwxMDEgTDE1NCw5OCBMMTYwLDEwNCBMMTU0LDExMCBMMTU0LDEwNyBMMTU0LDEwNyBaIE0xNTQsMTA3IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMTQ0LjAwMDAwMCwgLTk2LjAwMDAwMCkiIGZpbGw9IiM2OTY5NjkiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9zdmc+" width="36"/></td>
                    <td class="new_avatar" style="display: none;"><img class="item_photo_user thumb_profile new_thumb"/></td>
                </tr>
                <tr class="thumb_row">
                    <td class="current_avatar">
                        <? if($this->user->photo_id > 0): ?>
                            <img alt="" class="item_photo_user thumb_icon" id="previewimage" src="<?=$this->user->getPhotoUrl('thumb.icon');?>"/>
                        <? else: ?>
                            <img alt="" class="item_photo_user thumb_icon item_nophoto" id="previewimage" src="/application/modules/User/externals/images/nophoto_user_thumb_icon.png"/>
                        <? endif; ?>
                    </td>
                    <td class="middle_cell" style="display: none;"></td>
                    <td class="new_avatar" style="display: none;"><img class="item_photo_user thumb_icon new_icon"/><button class="photo-cancel"><?=$this->translate('Cancel');?></button></td>
                </tr>
            <tr>
                <td></td>
                <td></td>
                <td><div class="file-info"></div></td>
            </tr>
            </tbody>
        </table>
        <div id="thumbnail-controller" class="thumbnail-controller"<? if($this->user->photo_id == 0): ?> style="display: none;"<? endif; ?>>
            <span class="lasso-button"><?=$this->translate('Edit Thumbnail');?></span>
        </div>
        
        <div class="file_choose_block">
            <div class="file_choose_title"><?=$this->translate('Upload Photo');?></div>
            <div class="file_choose_instruction"><?=$this->translate('Drag an image here') . ' ' . $this->translate('or');?> <label class="file_label" for="Filedata"><?=$this->translate('select file from the list');?></label></div>
            <div class="file_choose_description"><?=$this->translate('File extensions');?>: jpeg, jpg, png, gif</div>
            <div class="file_choose_description"><?=$this->translate('Max file size');?> 15 MB</div>
            <input accept="image/jpeg,image/png,image/gif" id="Filedata" name="Filedata" type="file"/>
        </div>

        <div class="file_buttons">
            <button class="photo-update save" type="submit"><?=$this->translate('Save Photo');?></button>
            <? if($this->viewer->isSelf($this->user)): ?>
                <a class="button_link vk-photo save" href="<?=$this->vk_photo_link;?>"><i class="fa fa-vk"></i>  &nbsp;<?=$this->translate('Upload photo from VK');?></a>
            <? endif; ?>
            <button class="photo-remove next" onclick="RemoveUserPhoto(event)"<? if($this->user->photo_id == 0): ?> style="display: none;"<? endif; ?> type="button"><i class="fa fa-trash"></i> <?=$this->translate('Remove Photo');?></button>
        </div>

        <input id="imageurl" name="imageurl" type="hidden"/>
        <input id="coordinates" name="coordinates" type="hidden"/>
        <input id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" type="hidden" value="15728640"/><? /* 15728640 — 15 MB */ ?>
        
        <div style="text-align: center;">
            <button style="display: inline-block;" disabled='1' onclick="<?=htmlspecialchars(User_Form_Fields::$_finishAction)?>" data-type="2" type="button" id="finish" name="finish"><i class="fa fa-check"></i> &nbsp;<?=$this->translate('Finish');?></button>
        </div>
        
    </form>
</div>