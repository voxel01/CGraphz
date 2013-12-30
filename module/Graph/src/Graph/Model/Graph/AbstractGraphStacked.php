<?php
namespace Graph\Model\Graph;
abstract class AbstractGraphStacked extends AbstractGraph
{

    public function genGraphDefinition() {
        $sources = $this->getRrdSources();

        $i=0;
        /*echo "<pre>";
        echo "Files\n";
        var_dump($this->files);
        echo "TInstances\n";
        var_dump($this->tinstances);
        echo "\nDataSources\n";
        var_dump($this->dataSources);
        echo "\nSources\n";
        var_dump($sources);
        exit();*/
        if ($this->scale) {
            foreach ($this->tinstances as $tinstance) {
                foreach ($this->dataSources as $ds) {
                    $formatedSource=sprintf('%x', crc32($sources[$i]));
                    //echo "Source($i): $formatedSource \n";
                    $this->rrdRenderer->addDataSourceScaledDefinition('min_'.$formatedSource,$this->parseFilename($this->files[$tinstance]),$ds,'MIN',$this->scale)
                        ->addDataSourceScaledDefinition('avg_'.$formatedSource,$this->parseFilename($this->files[$tinstance]),$ds,'AVERAGE',$this->scale)
                        ->addDataSourceScaledDefinition('max_'.$formatedSource,$this->parseFilename($this->files[$tinstance]),$ds,'MAX',$this->scale);
                    $i++;
                }
            }
        }
        else {
            foreach ($this->tinstances as $tinstance) {
                foreach ($this->dataSources as $ds) {
                    $formatedSource=sprintf('%x', crc32($sources[$i]));
                    $this->rrdRenderer->addDataSourceDefinition('min_'.$formatedSource,$this->parseFilename($this->files[$tinstance]), $ds,'MIN')
                        ->addDataSourceDefinition('avg_'.$formatedSource,$this->parseFilename($this->files[$tinstance]), $ds,'AVERAGE')
                        ->addDataSourceDefinition('max_'.$formatedSource,$this->parseFilename($this->files[$tinstance]), $ds,'MAX');
                    $i++;
                }
            }
        }
        //exit();


        for ($i=count($sources)-1 ; $i>=0 ; $i--) {
            $formatedSource = $this->formatSource($sources[$i]);
            if ($i == (count($sources)-1))
                $this->rrdRenderer->addDataSourceCalculatedDefinition('area_'.$formatedSource,'avg_'.$formatedSource);
                //$rrdgraph[] = sprintf('CDEF:area_%s=avg_%1$s', crc32hex($sources[$i]));
            else
                $this->rrdRenderer->addDataSourceCalculatedDefinition('area_'.$formatedSource,'area_'.$this->formatSource($sources[$i+1]).',avg_'.$formatedSource.',ADDNAN');
                //$rrdgraph[] = sprintf('CDEF:area_%s=area_%s,avg_%1$s,ADDNAN', crc32hex($sources[$i]), crc32hex($sources[$i+1]));
        }
        $c = 0;
        foreach ($sources as $source) {
            $color = is_array($this->colors) ? (isset($this->colors[$source])?$this->colors[$source]:$this->colors[$c++]) : $this->colors;
            $this->rrdRenderer->addArea(sprintf('area_%s', $this->formatSource($source)),$this->getFadedColor($color));
            //$rrdgraph[] = sprintf('AREA:area_%s#%s', crc32hex($source), $color);
        }

        $lengths = array_map('strlen', $sources);
        $max_src = max($lengths);
        $max_src = $max_src > MAX_LEGEND_LENGTH ? MAX_LEGEND_LENGTH : $max_src;

        $lengths = array_map('strlen', $this->dataSourceNames);
        $max_ds = max($lengths);
        $max_ds = $max_ds > MAX_LEGEND_LENGTH ? MAX_LEGEND_LENGTH : $max_ds;

        $c = 0;
        foreach ($sources as $source) {
            if (empty($this->dataSourceNames[$source])) {
                //$dsname =  sprintf('%1$-'.$max_src.'s', $source);
                $dsname = sprintf('%1$-'.$max_src.'s',preg_replace('/\s+?(\S+)?$/u', '', mb_substr($source, 0, $max_src)));
            } else {
                //$dsname = sprintf('%1$-'.$max_ds.'s', $this->ds_names[$source]);
                $dsname = sprintf('%1$-'.$max_ds.'s',preg_replace('/\s+?(\S+)?$/u', '', mb_substr($this->dataSourceNames[$source], 0, $max_ds)));
            }
            //$dsname = empty($this->ds_names[$source]) ? $source : $this->ds_names[$source];
            $color = is_array($this->colors) ? (isset($this->colors[$source])?$this->colors[$source]:$this->colors[$c++]): $this->colors;

            $formatedSource=sprintf('%x', crc32($sources[$i]));
            $this->rrdRenderer->addLine('area_'.$formatedSource,$color,$this->rrdEscape(ucfirst(str_replace('_', ' ',$dsname))))
                ->addGprint('min_'.$formatedSource,$this->rrdFormat.' Min','MIN')
                ->addGprint('avg_'.$formatedSource,$this->rrdFormat.' Avg','AVERAGE')
                ->addGprint('max_'.$formatedSource,$this->rrdFormat.' Min','MAX')
                ->addGprint('avg_'.$formatedSource,$this->rrdFormat.' Last\\l','LAST');
        }
    }
}
