import { registerBlockType, setCategories } from '@wordpress/blocks';
import MailPoet from 'mailpoet';

import * as email from './email/email.jsx';
import * as submit from './submit/submit.jsx';
import * as firstName from './first_name/first_name.jsx';
import * as lastName from './last_name/last_name.jsx';
import * as segmentSelect from './segment_select/segment_select.jsx';

export default () => {
  setCategories([
    { slug: 'obligatory', title: '' }, // Blocks from this category are not in block insert popup
    { slug: 'fields', title: MailPoet.I18n.t('fieldsBlocksCategory') },
  ]);

  registerBlockType(email.name, email.settings);
  registerBlockType(submit.name, submit.settings);
  registerBlockType(firstName.name, firstName.settings);
  registerBlockType(lastName.name, lastName.settings);
  registerBlockType(segmentSelect.name, segmentSelect.settings);
};
