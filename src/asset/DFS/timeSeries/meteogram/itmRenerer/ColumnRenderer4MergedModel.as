package asset.DFS.timeSeries.meteogram.itmRenerer
{
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	import mx.charts.ChartItem;
	import mx.charts.chartClasses.LegendData;
	import mx.charts.series.ColumnSeries;
	import mx.charts.series.items.ColumnSeriesItem;
	import mx.controls.Label;
	import mx.core.IDataRenderer;
	import mx.core.UIComponent;
	import mx.formatters.NumberFormatter;
	
	public class ColumnRenderer4MergedModel extends UIComponent implements IDataRenderer
	{
		private var label:Label;
		private var _chartItem:ChartItem; // stores current ColumnSeriesItem

	    public function ColumnRenderer4MergedModel():void
	    {
	        super();	        
	    }

		override protected function createChildren():void
		{	
			super.createChildren();
			if (label == null)
	        {
				// create and add the label
		        label = new Label();
		        label.truncateToFit = true;	
		        label.setStyle("fontSize", 12);	      
		        label.setStyle("textAlign", "center");
//		        addChild(label);
	        }	        
		}
				
		public function set data(value:Object):void
	    {
	        if (_chartItem == value) return;
	          // setData also is executed if there is a Legend Data 
	          // defined for the chart. We validate that only chartItems are 
	          // assigned to the chartItem class. 
	        if (value is LegendData) 
	        	return;
			
	        _chartItem = ChartItem(value);	        
	    }	
		
	    public function get data():Object
	    {
	        return _chartItem;
	    }
                                         
    	override protected function updateDisplayList(unscaledWidth:Number,unscaledHeight:Number):void
  		{
	        super.updateDisplayList(unscaledWidth, unscaledHeight);
	        
	        var rc:Rectangle = new Rectangle(0, 1, width, height);	       
	        var g:Graphics = graphics;
	        g.clear();        
	        g.moveTo(rc.left, rc.top);
	         
	         
	         
	         
	         
	        if (_chartItem == null) // _chartItem has no data
	           return;
	           
	        var cs:ColumnSeries = _chartItem.element as ColumnSeries;
	        var csi:ColumnSeriesItem = _chartItem as ColumnSeriesItem;
			
			// set the label	
			var currFormat:Object = new NumberFormatter();
			currFormat.precision = 0;
			currFormat.rounding = "nearest";
			var val:Number = csi.item[cs.yField];		       	
			label.text =  currFormat.format(val)+"";       		
			
	       	label.width = label.maxWidth = unscaledWidth;	       	
	       	label.height = label.textHeight;
			var labelHeight:int = label.textHeight + 2;
				       		        	 
	        // label's default y is 0. if the bar is too short we need to move it up a bit
	       	var barYpos:Number = csi.y;
	       	var minYpos:Number = csi.min;
	       	
//	       	trace("[csi.y] " +barYpos);
//	       	trace("[csi.min] " +minYpos);
			var barHeight:Number = minYpos - barYpos;
			var labelColor:uint = 0xFFFFFF; // white
	       
//	       	if (barHeight < labelHeight) // if no room for label
//	       	{
				// nudge label up the amount of pixels missing
	       		label.y = -20; // -1 * (labelHeight - barHeight);
	       		labelColor = 0x222222; // label will appear on white background, so make it dark	       		
//	       	}
//	       	else
//			{	
//				// center the label vertically in the bar
//	       		label.y =barHeight-labelHeight*5;     // barHeight / 2  - labelHeight / 2;
//	       		labelColor = 0x000099; //blue
//			}
			
			label.setStyle("color", labelColor);
	       
	       
	       var csColor:int = (cs.getStyle("fill")).color;
	        var columnTransparency:Number = (cs.getStyle("alpha"));
			var borderStrokeWeight:int = (cs.getStyle("stroke")).alpha;
	       
	       
			// Draw the column
			if(Number(borderStrokeWeight) !=0){
				if(Number(label.text) != 0){
					g.lineStyle(1,0x000000,1);								//alpha     ratio
				}
			}
			g.beginFill(csColor,columnTransparency);
	        g.lineTo(rc.right,rc.top);
	        g.lineTo(rc.right,rc.bottom);
	        g.lineTo(rc.left,rc.bottom);
	        g.lineTo(rc.left,rc.top);
	        g.endFill();   
  		}
	}
}



